<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Services\AppraisalWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

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
                ->color('success')
                ->icon('heroicon-m-check-circle')
                ->visible(fn ($record) => 
                    $record->status === 'submitted' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                    // REMOVED: !empty check. We rely on form validation instead.
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Evaluation')
                ->modalDescription('This will finalize your evaluation and forward the appraisal.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save(); // Validation happens here
                    $workflow->assess($this->getRecord()); 
                    Notification::make()->title('Assessment Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // ACTION 2: Submit Regional Review (Part C)
            // Visible ONLY for Regional Head when reviewing Chapter Employees
            Actions\Action::make('submit_regional_review')
                ->label('Submit Regional Review')
                ->color('success')
                ->icon('heroicon-m-check-badge')
                ->visible(fn ($record) => 
                    $record->status === 'regional_head_review_pending' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                    // REMOVED: filled() check. Button is now always visible at this stage.
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Regional Review')
                ->modalDescription('This will finalize your review and forward the application to the DG & CEO.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save(); 
                    $workflow->reviewRegional($this->getRecord()); 
                    Notification::make()->title('Regional Review Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
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

    protected function getRedirectUrl(): string
    {
        // Keeps user on the page after saving draft so they can hit Submit
        return $this->getResource()::getUrl('edit', ['record' => $this->getRecord()]);
    }
}