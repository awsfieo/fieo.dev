<?php

namespace App\Filament\Employee\Resources\TourClaims\Pages;

use App\Filament\Employee\Resources\TourClaims\TourClaimResource;
use App\Services\TourClaimWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification; // Import the Facade

class ViewTourClaim extends ViewRecord
{
    protected static string $resource = TourClaimResource::class;

   protected function getHeaderActions(): array
    {
        return [
            // 1. Submit Action (Logic: Visible if Draft + Created by Me)
            Actions\Action::make('submit')
                ->label('Submit for Review')
                ->color('primary')
                ->icon('heroicon-m-paper-airplane')
                // FIX: Check employee_id (Ownership) instead of pending_with for Drafts
                ->visible(fn ($record) => 
                    $record->current_state === 'draft' && 
                    $record->employee_id === Auth::user()->employee?->id
                )
                ->requiresConfirmation()
                ->action(function ($record, TourClaimWorkflow $workflow) {
                    $workflow->submit($record);
                    Notification::make()->title('Claim submitted successfully!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),

            // 2. Forward Action (Logic: Visible if Pending With Me)
            Actions\Action::make('forward')
                ->label('Recommend / Forward')
                ->color('success')
                ->icon('heroicon-m-arrow-right-circle')
                // Keep this: Checks if pending_with matches your EMPLOYEE CODE
                ->visible(fn ($record) => $record->pending_with === Auth::user()->employee?->employee_code)
                ->form([
                    Textarea::make('remarks')->required()->label('Remarks')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->forward($record, $data['remarks']);
                    Notification::make()->title('Claim forwarded.')->success()->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),

            // 3. Approve Action
            Actions\Action::make('approve')
                ->label('Final Approval')
                ->color('success')
                ->icon('heroicon-m-check-badge')
                ->visible(fn ($record) => $record->pending_with === Auth::user()->employee?->employee_code)
                ->requiresConfirmation()
                ->form([
                    Textarea::make('remarks')->label('Approval Note')->default('Approved.')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->approve($record, $data['remarks']);
                    Notification::make()->title('Claim Approved!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),

            // 4. Reject Action
            Actions\Action::make('reject')
                ->label('Return / Reject')
                ->color('danger')
                ->icon('heroicon-m-x-circle')
                ->visible(fn ($record) => $record->pending_with === Auth::user()->employee?->employee_code)
                ->form([
                    Textarea::make('reason')->required()->label('Reason')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->reject($record, $data['reason']);
                    Notification::make()->title('Claim returned to employee.')->danger()->send();
                    $this->redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),

            Actions\EditAction::make()
                ->visible(fn ($record) => $record->current_state === 'draft'),
        ];
    }
}