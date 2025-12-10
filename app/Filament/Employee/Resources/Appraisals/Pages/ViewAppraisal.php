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
            // 1. SUBMIT ACTION (For Employee - Draft Only)
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
                ->modalDescription('This will lock the form and forward it to your Reporting Officer.')
                ->action(function (AppraisalWorkflow $workflow) {
                    $workflow->submit($this->getRecord());
                    Notification::make()->title('Appraisal Submitted Successfully')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 2. EDIT ACTION (Draft Only)
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'draft'),

            // 3. ASSESS ACTION (Reporting Officer)
            Actions\Action::make('assess')
                ->label('Fill Assessment')
                ->color('warning')
                ->icon('heroicon-m-pencil-square')
                ->visible(fn ($record) => 
                    $record->status === 'submitted' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                // Redirect to Edit page to fill Form B
                ->url(fn ($record) => $this->getResource()::getUrl('edit', ['record' => $record])),

            // 4. REGIONAL REVIEW ACTION
            Actions\Action::make('regional_review')
                ->label('Regional Review')
                ->color('orange')
                ->visible(fn ($record) => 
                    $record->status === 'regional_head_review_pending' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->url(fn ($record) => $this->getResource()::getUrl('edit', ['record' => $record])),

            // 5. FINALIZE (DG)
            Actions\Action::make('finalize')
                ->label('Finalize')
                ->color('primary')
                ->visible(fn ($record) => 
                    $record->status === 'final_review_pending' && 
                    Auth::user()->hasRole('DG & CEO')
                )
                ->url(fn ($record) => $this->getResource()::getUrl('edit', ['record' => $record])),
        ];
    }
}