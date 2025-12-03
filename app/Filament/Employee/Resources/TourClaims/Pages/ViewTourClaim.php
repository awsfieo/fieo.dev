<?php

namespace App\Filament\Employee\Resources\TourClaims\Pages;

use App\Filament\Employee\Resources\TourClaims\TourClaimResource;
use App\Services\TourClaimWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;

class ViewTourClaim extends ViewRecord
{
    protected static string $resource = TourClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Submit (Draft -> Submitted)
            Actions\Action::make('submit')
                ->label('Submit for Review')
                ->color('primary')
                ->icon('heroicon-m-paper-airplane')
                ->visible(fn ($record) => $record->current_state === 'draft' && $record->employee_id === Auth::user()->employee?->id)
                ->requiresConfirmation()
                ->action(function ($record, TourClaimWorkflow $workflow) {
                    $workflow->submit($record);
                    $this->notify('success', 'Claim submitted successfully!');
                    $this->refreshFormData(['current_state', 'pending_with']);
                }),

            // 2. Forward (Submitted -> Next Stage)
            Actions\Action::make('forward')
                ->label('Recommend / Forward')
                ->color('success')
                ->icon('heroicon-m-arrow-right-circle')
                ->visible(fn ($record) => $record->pending_with === Auth::user()->employee?->id)
                ->form([
                    Textarea::make('remarks')->required()->label('Remarks')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->forward($record, $data['remarks']);
                    $this->notify('success', 'Claim forwarded.');
                    $this->refreshFormData(['current_state', 'pending_with']);
                }),

            // 3. Approve (Final)
            Actions\Action::make('approve')
                ->label('Final Approval')
                ->color('success')
                ->icon('heroicon-m-check-badge')
                ->visible(fn ($record) => $record->pending_with === Auth::user()->employee?->id)
                ->requiresConfirmation()
                ->form([
                    Textarea::make('remarks')->label('Approval Note')->default('Approved.')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->approve($record, $data['remarks']);
                    $this->notify('success', 'Claim Approved!');
                    $this->refreshFormData(['current_state', 'pending_with']);
                }),

            // 4. Reject (Back to Draft)
            Actions\Action::make('reject')
                ->label('Return / Reject')
                ->color('danger')
                ->icon('heroicon-m-x-circle')
                ->visible(fn ($record) => $record->pending_with === Auth::user()->employee?->id)
                ->form([
                    Textarea::make('reason')->required()->label('Reason')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->reject($record, $data['reason']);
                    $this->notify('danger', 'Claim returned to employee.');
                    $this->refreshFormData(['current_state', 'pending_with']);
                }),

            Actions\EditAction::make()
                ->visible(fn ($record) => $record->current_state === 'draft'),
        ];
    }
    
    // Filament V3/4 Helper
    protected function notify($status, $title)
    {
        \Filament\Notifications\Notification::make()->title($title)->status($status)->send();
    }
}