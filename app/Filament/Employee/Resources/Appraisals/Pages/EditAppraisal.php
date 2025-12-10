<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Services\AppraisalWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAppraisal extends EditRecord
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
            
            // Optional: Manager quick-submit from Edit page
            Actions\Action::make('save_and_submit_assessment')
                ->label('Submit Assessment')
                ->color('success')
                ->visible(fn ($record) => 
                    $record->status === 'submitted' && 
                    $record->pending_with === \Illuminate\Support\Facades\Auth::user()->employee?->employee_code
                )
                ->requiresConfirmation()
                ->action(function (AppraisalWorkflow $workflow) {
                    $this->save();
                    $workflow->assess($this->getRecord());
                    Notification::make()->title('Assessment Submitted')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to View page so Employee sees the "Submit" button
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}