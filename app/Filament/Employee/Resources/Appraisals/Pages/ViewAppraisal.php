<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Services\AppraisalWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewAppraisal extends ViewRecord
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // --- 1. SUBMIT (Employee) ---
            Actions\Action::make('submit')
                ->label('Submit Appraisal')
                ->color('success')
                ->icon('heroicon-m-paper-airplane')
                ->visible(fn ($record) => 
                    $record->status === 'draft' && 
                    $record->employee_id === Auth::user()->employee?->id
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Appraisal?')
                ->modalDescription('Once submitted, the form will be locked and forwarded to your Reporting Officer.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $workflow->submit($this->getRecord());
                    Notification::make()->title('Appraisal Submitted Successfully')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // --- 2. ASSESS (Reporting Officer) ---
            Actions\Action::make('submit_assessment')
                ->label('Submit Assessment')
                ->color('warning')
                ->icon('heroicon-m-check-badge')
                ->visible(fn ($record) => 
                    $record->status === 'submitted' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->action(function (AppraisalWorkflow $workflow) {
                    $workflow->assess($this->getRecord());
                    Notification::make()->title('Assessment Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // --- 3. REGIONAL REVIEW ---
            Actions\Action::make('submit_regional_review')
                ->label('Submit Regional Review')
                ->color('orange')
                ->visible(fn ($record) => 
                    $record->status === 'regional_head_review_pending' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->action(function (AppraisalWorkflow $workflow) {
                    $workflow->reviewRegional($this->getRecord());
                    Notification::make()->title('Review Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // --- 4. FINALIZE (DG) ---
            Actions\Action::make('finalize_appraisal')
                ->label('Finalize & Close')
                ->color('primary')
                ->icon('heroicon-m-lock-closed')
                ->visible(fn ($record) => 
                    $record->status === 'final_review_pending' && 
                    Auth::user()->hasRole('DG & CEO')
                )
                ->requiresConfirmation()
                ->action(function (AppraisalWorkflow $workflow) {
                    $workflow->finalize($this->getRecord());
                    Notification::make()->title('Appraisal Finalized')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Standard Edit (Only if Draft)
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),
        ];
    }
}