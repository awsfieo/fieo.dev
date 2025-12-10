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
            // Actions\DeleteAction::make(), // <--- REMOVED: No one should delete an active appraisal
            
            // 1. Submit Action (For Reporting Officer / Managers)
            Actions\Action::make('submit_assessment')
                ->label('Submit Assessment')
                ->color('success')
                ->icon('heroicon-m-check-circle')
                // Visible only if status is NOT draft (meaning it's in review) AND user is the pending actor
                ->visible(fn ($record) => 
                    $record->status !== 'draft' && 
                    $record->status !== 'closed' &&
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->modalHeading('Submit Assessment')
                ->modalDescription('This will finalize your evaluation and forward the appraisal to the next authority. You cannot edit it afterwards.')
                ->action(function (AppraisalWorkflow $workflow) {
                    // Save current data first
                    $this->save(); 
                    
                    // Trigger Workflow
                    $workflow->assess($this->getRecord());
                    
                    Notification::make()->title('Assessment Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    // 2. Customize the Bottom "Save" Button to avoid confusion
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label('Save Draft') // Renamed from "Save"
                ->submit('save'),
                
            $this->getCancelFormAction(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to View page so Employee sees the "Submit" button
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}