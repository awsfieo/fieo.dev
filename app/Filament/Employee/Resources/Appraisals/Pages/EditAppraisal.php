<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Services\AppraisalWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditAppraisal extends EditRecord
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. SUBMIT ACTION (For Employee)
            // Only visible if status is 'draft' AND user is the owner
            Actions\Action::make('submit')
                ->label('Submit Appraisal')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn ($record) => 
                    $record->status === 'draft' && 
                    $record->employee_code === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Appraisal?')
                ->modalDescription('Once submitted, you will not be able to edit this form. It will be forwarded to your Reporting Officer.')
                ->action(function (AppraisalWorkflow $workflow) {
                    // 1. Save any pending changes
                    $this->save(); 
                    
                    // 2. Trigger Workflow
                    $workflow->submit($this->getRecord());

                    // 3. Notify & Redirect
                    Notification::make()->title('Appraisal Submitted Successfully')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 2. ASSESS ACTION (For Reporting Officer - HOD/Chapter Head)
            // Visible if status is 'submitted' AND 'pending_with' matches current user
            Actions\Action::make('submit_assessment')
                ->label('Submit Assessment')
                ->color('warning')
                ->icon('heroicon-o-check-badge')
                ->visible(fn ($record) => 
                    $record->status === 'submitted' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Common Evaluation?')
                ->modalDescription('This assessment will be forwarded to the next authority.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save();
                    $workflow->assess($this->getRecord());
                    
                    Notification::make()->title('Assessment Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 3. REGIONAL REVIEW ACTION (For Regional Head reviewing Chapter Head)
            Actions\Action::make('submit_regional_review')
                ->label('Submit Regional Review')
                ->color('orange')
                ->icon('heroicon-o-globe-alt')
                ->visible(fn ($record) => 
                    $record->status === 'regional_head_review_pending' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save();
                    $workflow->reviewRegional($this->getRecord());
                    
                    Notification::make()->title('Regional Review Completed')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 4. FINALIZE ACTION (For DG & CEO)
            Actions\Action::make('finalize_appraisal')
                ->label('Finalize & Close')
                ->color('primary')
                ->icon('heroicon-o-lock-closed')
                ->visible(fn ($record) => 
                    $record->status === 'final_review_pending' && 
                    Auth::user()->hasRole('DG & CEO')
                )
                ->requiresConfirmation()
                ->modalHeading('Finalize Appraisal?')
                ->modalDescription('This will lock the appraisal and record the annual increment.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save();
                    $workflow->finalize($this->getRecord());
                    
                    Notification::make()->title('Appraisal Finalized')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Standard Delete Action (Only for Drafts)
            Actions\DeleteAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }

    // Optional: Customize the redirect after standard "Save" button
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}