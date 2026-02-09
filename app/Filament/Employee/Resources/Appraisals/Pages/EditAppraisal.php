<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Services\AppraisalWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Support\Htmlable;
use App\Models\Appraisal;

class EditAppraisal extends EditRecord
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),

            // ACTION 1: Submit Common Assessment (Part B)
            // Visible for Regional Head (when acting as RO) or Chapter Head
            Actions\Action::make('submit_assessment')
                ->label('Submit Assessment')
                ->color('warning')
                ->icon('heroicon-m-check-circle')
                ->visible(
                    fn($record) =>
                    $record->status === 'submitted' &&
                        $record->pending_with === Auth::user()->employee?->employee_code
                    // REMOVED: !empty check. We rely on form validation instead.
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Evaluation')
                ->modalDescription('This will finalise your evaluation and forward the appraisal.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save(); 

                    $record = $this->getRecord();
                    
                    // --- REFACTOR START: Use Centralized Logic ---
                    // Fetch the saved ratings (or empty array if null)
                    $ratings = $record->evaluation_form_data['ratings'] ?? [];
                    
                    // Calculate using the Model
                    $average = \App\Models\Appraisal::calculateCompetencyScore($ratings);
                    // --- REFACTOR END ---

                    // Validate
                    if ($average < 3) {
                        Notification::make()
                            ->title('Cannot Submit Assessment')
                            ->body("The average competency score is " . number_format($average, 2) . ". It must be at least 3.0 to submit.")
                            ->danger()
                            ->persistent()
                            ->send();

                        $this->halt();
                        return;
                    }

                    $workflow->assess($this->getRecord());
                    Notification::make()->title('Assessment Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // ACTION 2: Submit Regional Review (Part C)
            // Visible ONLY for Regional Head when reviewing Chapter Employees
            Actions\Action::make('submit_regional_review')
                ->label('Submit Regional Head Review')
                ->color('warning')
                ->icon('heroicon-m-check-badge')
                ->visible(
                    fn($record) =>
                    $record->status === 'regional_head_review_pending' &&
                        $record->pending_with === Auth::user()->employee?->employee_code
                    // REMOVED: filled() check. Button is now always visible at this stage.
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Regional Head Review')
                ->modalDescription('This will finalise your review and forward the appraisal to the DG & CEO')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save();
                    $workflow->reviewRegional($this->getRecord());
                    Notification::make()->title('Regional Head Review Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // --- 3. ADD THIS: Submit Final Review (Part D) ---
            Actions\Action::make('submit_final_review')
                ->label('Complete the Appraisal')
                ->color('success')
                ->icon('heroicon-m-check-badge')
                // Visible only if status is 'final_assessment_pending' and user is DG
                ->visible(
                    fn($record) =>
                    $record->status === 'final_assessment_pending' &&
                        Auth::user()->hasRole('DG & CEO')
                )
                ->requiresConfirmation()
                ->modalHeading('Complete the Appraisal?')
                ->modalDescription('This will close the appraisal process and mark it as completed. This action cannot be undone.')
                ->action(function (AppraisalWorkflow $workflow) {
                    // 1. Save the form data first
                    $this->save();

                    // 2. Trigger the workflow
                    $workflow->finalize($this->getRecord());

                    // 3. Notify and Redirect
                    Notification::make()->title('Appraisal Completed')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    public function getTitle(): string | Htmlable
    {
        // Uses the relationship to get the real name
        return $this->getRecord()->employee->name ?? 'Edit Appraisal';
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Draft')
                ->submit('save'),
            $this->getCancelFormAction(),
        ];
    }

    protected function afterSave(): void
    {
        // Refresh the record to ensure the latest data is ready for the redirect
        $this->getRecord()->refresh();
    }

    protected function getRedirectUrl(): string
    {
        // FIX: Append a timestamp query parameter (?t=...)
        // This forces the browser/Livewire to load the page as fresh content
        // instead of restoring a stale "back-cache" version.
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]) . '?t=' . time();
    }
}
