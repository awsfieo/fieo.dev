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
            Actions\Action::make('submit')
                ->label('Submit Appraisal')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                // FIX: Relaxed visibility check
                ->visible(function ($record) {
                    // Check 1: Must be draft
                    $isDraft = $record->status === 'draft';
                    
                    // Check 2: Must be the owner
                    // We compare IDs to be safer than string codes
                    $isOwner = $record->employee_id === Auth::user()->employee?->id;

                    return $isDraft && $isOwner;
                })
                ->requiresConfirmation()
                ->modalHeading('Submit Appraisal?')
                ->modalDescription('Once submitted, you cannot edit this form. It will be forwarded to your Reporting Officer.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save(); // Save data first
                    $workflow->submit($this->getRecord());
                    
                    Notification::make()->title('Appraisal Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 2. ASSESS ACTION (For Reporting Officer)
            Actions\Action::make('submit_assessment')
                ->label('Submit Assessment')
                ->color('warning')
                ->icon('heroicon-o-check-badge')
                ->visible(fn ($record) => 
                    $record->status === 'submitted' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
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