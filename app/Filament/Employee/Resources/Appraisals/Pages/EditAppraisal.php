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
use App\Ai\Agents\AppraisalSummaryAgent;

class EditAppraisal extends EditRecord
{
    protected static string $resource = AppraisalResource::class;

    public function mount(int|string $record): void
    {
        // 1. Load the record normally
        $this->record = $this->resolveRecord($record);

        // 2. Check if Summary exists. If empty, generate it automatically.
        $currentSummary = $this->record->final_assessment_data['ai_summary'] ?? '';

        if (empty($currentSummary)) {
            try {
                // --- CONTEXT BUILDING (Matches your AppraisalForm logic) ---

                // Calculate Score
                $ratings = $this->record->evaluation_form_data['ratings'] ?? [];
                $score = 'N/A';
                $filledRatings = array_filter($ratings, fn($val) => is_numeric($val) && $val > 0);
                if (count($filledRatings) > 0) {
                    $average = array_sum($filledRatings) / count($filledRatings);
                    $score = number_format($average, 1) . ' / 10';
                }

                // Build Context String
                $context = "Job Satisfaction: " . ($this->record->appraisal_form_data['job_satisfaction'] ?? 'Not Provided') . "\n";
                $context .= "Key Achievements: " . strip_tags($this->record->appraisal_form_data['achievements'] ?? 'N/A') . "\n";
                $context .= "Training Needs: " . strip_tags($this->record->appraisal_form_data['training_needs'] ?? 'N/A') . "\n";
                $context .= "Areas of Dissatisfaction: " . strip_tags($this->record->appraisal_form_data['area_of_dissatisfaction'] ?? 'N/A') . "\n";

                $context .= "COMPETENCY SCORE: " . $score . "\n";

                $context .= "Evaluator Assessment: " . strip_tags($this->record->evaluation_form_data['overall_assessment'] ?? 'N/A') . "\n";
                $context .= "Evaluator Agreement: " . ($this->record->evaluation_form_data['agree_with_employee'] ?? 'N/A') . "\n";

                if (($this->record->evaluation_form_data['agree_with_employee'] ?? '') === 'No') {
                    $context .= "Disagreements: " . strip_tags($this->record->evaluation_form_data['disagreement'] ?? '') . "\n";
                }

                $context .= "Regional Head Comments: " . strip_tags($this->record->regional_head_review_data['comments'] ?? 'N/A');

                // --- AI GENERATION ---
                $response = (new AppraisalSummaryAgent)->prompt($context);

                // Clean formatting
                $cleanHtml = str($response)
                    ->replace(['```html', '```'], '')
                    ->trim()
                    ->toString();

                // --- SAVE TO DB ---
                $data = $this->record->final_assessment_data ?? [];
                $data['ai_summary'] = $cleanHtml;
                $this->record->update(['final_assessment_data' => $data]);

                // Notify User
                Notification::make()
                    ->title('AI Summary Ready')
                    ->body('An executive summary has been auto-generated for you.')
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                // Fail silently on auto-load so we don't crash the page if AI fails
            }
        }

        // 3. Continue with parent mount
        parent::mount($record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),

            // ACTION 1: Submit Common Assessment (Part B)
            // Visible for Regional Head (when acting as RO) or Chapter Head
            // ACTION 1: Submit Common Assessment (Part B)
            Actions\Action::make('submit_assessment')
                // 1. DYNAMIC LABEL & COLOR
                ->label(fn() => Auth::user()->hasRole('DG & CEO') ? 'Complete the Appraisal' : 'Submit Assessment')
                ->color(fn() => Auth::user()->hasRole('DG & CEO') ? 'success' : 'warning')
                ->icon('heroicon-m-check-circle')

                // 2. VISIBILITY
                ->visible(
                    fn($record) =>
                    // Visible if status is 'submitted' OR 'final_assessment_pending'
                    in_array($record->status, ['submitted', 'final_assessment_pending']) &&
                        $record->pending_with === Auth::user()->employee?->employee_code
                )

                // 3. CONFIRMATION DIALOG
                ->requiresConfirmation()
                ->modalHeading(fn() => Auth::user()->hasRole('DG & CEO') ? 'Finalise & Close Appraisal?' : 'Submit Evaluation')
                ->modalDescription(fn() => Auth::user()->hasRole('DG & CEO')
                    ? 'This will finalise the appraisal process and mark it as Closed. This action cannot be undone.'
                    : 'This will submit your evaluation and forward the appraisal to the next stage.')

                // 4. ACTION LOGIC
                ->action(function (AppraisalWorkflow $workflow) {

                    if (Auth::user()->hasRole('DG & CEO')) {
                        // --- DG LOGIC: Finalize ---

                        // A. Save Data First 
                        // (This triggers mutateFormDataBeforeSave, which handles the Auto-Fill Logic automatically)
                        $this->save();
                        $record = $this->getRecord();

                        // B. Validate Final Increment is selected (Extra Safety)
                        if (empty($record->final_increment)) {
                            Notification::make()
                                ->title('Missing Increment')
                                ->body('Please select the Final Increment % in the Final Assessment tab.')
                                ->danger()
                                ->send();

                            // Redirect to Final Assessment tab to fix it
                            $this->redirect($this->getResource()::getUrl('edit', ['record' => $record]) . '?tab=final-assessment');
                            return;
                        }

                        // C. Score Validation
                        $ratings = $record->evaluation_form_data['ratings'] ?? [];
                        $average = \App\Models\Appraisal::calculateCompetencyScore($ratings);

                        if ($average < 3) {
                            Notification::make()->title('Cannot Submit')->body("Average score {$average} is too low.")->danger()->send();
                            $this->halt();
                            return;
                        }

                        // D. Finalize & Close
                        $workflow->finalize($record);
                        Notification::make()->title('Appraisal Completed & Closed')->success()->send();
                    } else {
                        // --- STANDARD LOGIC: Submit to Next Level ---

                        $this->save();
                        $record = $this->getRecord();

                        // Score Validation
                        $ratings = $record->evaluation_form_data['ratings'] ?? [];
                        $average = \App\Models\Appraisal::calculateCompetencyScore($ratings);

                        if ($average < 3) {
                            Notification::make()->title('Cannot Submit')->body("Average score {$average} is too low.")->danger()->send();
                            $this->halt();
                            return;
                        }

                        $workflow->assess($record);
                        Notification::make()->title('Assessment Submitted')->success()->send();
                    }

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
            // Actions\Action::make('submit_final_review')
            //     ->label('Complete the Appraisal')
            //     ->color('success')
            //     ->icon('heroicon-m-check-badge')
            //     // Visible only if status is 'final_assessment_pending' and user is DG
            //     ->visible(
            //         fn($record) =>
            //         $record->status === 'final_assessment_pending' &&
            //             Auth::user()->hasRole('DG & CEO')
            //     )
            //     ->requiresConfirmation()
            //     ->modalHeading('Complete the Appraisal?')
            //     ->modalDescription('This will close the appraisal process and mark it as completed. This action cannot be undone.')
            //     ->action(function (AppraisalWorkflow $workflow) {
            //         // 1. Save the form data first
            //         $this->save();

            //         // 2. Trigger the workflow
            //         $workflow->finalize($this->getRecord());

            //         // 3. Notify and Redirect
            //         Notification::make()->title('Appraisal Completed')->success()->send();
            //         $this->redirect($this->getResource()::getUrl('index'));
            //     }),
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

    // Add this method to your EditAppraisal class to intercept the Save Draft button
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // FIX: Only run this "Auto-Complete" logic for DG & CEO.
        // For Chapter Heads/HODs, we want standard behavior (Stay on 'submitted').
        if (Auth::user()->hasRole('DG & CEO') && $this->getRecord()->status === 'submitted') {

            // 1. Calculate Average Score
            $ratings = $data['evaluation_form_data']['ratings'] ?? [];
            $average = Appraisal::calculateCompetencyScore($ratings);

            // 2. Determine Increment %
            $increment = '0%';
            if ($average >= 9) {
                $increment = '10%';
            } elseif ($average >= 7) {
                $increment = '7%';
            } elseif ($average >= 5) {
                $increment = '5%';
            } elseif ($average > 0) {
                $increment = '0%';
            }

            // 3. Auto-Fill Final Assessment Data
            $finalData = $data['final_assessment_data'] ?? [];
            $finalData['agree_with_evaluation'] = 'Yes';
            $finalData['disagreement'] = null;
            $finalData['comments'] = $data['evaluation_form_data']['overall_assessment'] ?? '';

            $data['final_assessment_data'] = $finalData;
            $data['final_increment'] = $increment;

            // 4. Update Status (Only for DG)
            // $data['status'] = 'final_assessment_pending';
        }

        return $data;
    }

    /**
     * This runs immediately after the "Save" / "Save Draft" button persists data.
     */
    protected function afterSave(): void
    {
        // FIX: Removed duplicate logic. 
        // The work is now done safely in mutateFormDataBeforeSave (for DG only).
        // Standard users rely on the 'Submit Assessment' button action for status changes.
        $this->getRecord()->refresh();
    }

    /**
     * Handle the Redirection Logic
     */
    protected function getRedirectUrl(): string
    {
        $record = $this->getRecord();
        $record->refresh(); // Ensure we have the latest data

        // 1. DG & CEO OVERRIDE:
        // If the DG is working on an appraisal (Status is 'submitted' or 'final_assessment_pending'),
        // ALWAYS redirect to the "Final Assessment" tab after saving. 
        // This ensures that after they click "Save Draft" on the Evaluation Form, 
        // they are immediately taken to the Final Assessment tab to see the auto-filled data.
        if (Auth::user()->hasRole('DG & CEO')) {
            if (in_array($record->status, ['submitted', 'final_assessment_pending'])) {
                return $this->getResource()::getUrl('edit', ['record' => $record]) . '?tab=final-assessment';
            }
        }

        // 2. Standard Logic for Everyone Else (HOD, Regional Head, etc.)
        // This keeps them on their respective active tab based on status.
        $tab = match ($record->status) {
            'submitted'                    => 'evaluation-form',
            'regional_head_review_pending' => 'regional-head-review',
            'final_assessment_pending'     => 'final-assessment',
            default                        => null,
        };

        if ($tab) {
            return $this->getResource()::getUrl('edit', ['record' => $record]) . "?tab={$tab}";
        }

        return $this->getResource()::getUrl('index');
    }
}
