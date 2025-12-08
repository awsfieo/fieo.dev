<?php

namespace App\Filament\Employee\Resources\TourClaims\Pages;

use App\Filament\Employee\Resources\TourClaims\TourClaimResource;
use App\Services\TourClaimWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewTourClaim extends ViewRecord
{
    protected static string $resource = TourClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Submit Action
            Actions\Action::make('submit')
                ->label('Submit for Review')
                ->color('primary')
                ->icon('heroicon-m-paper-airplane')
                ->visible(
                    fn($record) =>
                    in_array($record->current_state, ['draft', 'query']) &&
                        $record->employee_id === Auth::user()->employee?->id
                )
                ->requiresConfirmation()
                ->action(function ($record, TourClaimWorkflow $workflow) {
                    $workflow->submit($record);
                    Notification::make()->title('Claim submitted successfully!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 2. Forward Action (Visible if Pending with Me AND Next Actor Exists)
            Actions\Action::make('forward')
                ->label('Recommend / Forward')
                ->color('success')
                ->icon('heroicon-m-arrow-right-circle')
                ->visible(function ($record, TourClaimWorkflow $workflow) {
                    // 1. Must be pending with me
                    if ($record->pending_with !== Auth::user()->employee?->employee_code) {
                        return false;
                    }

                    // 2. Check workflow: Is there a next person?
                    $nextActor = $workflow->determineNextActor($record, 'Forward', Auth::user());

                    // Show Forward ONLY if there IS a next actor
                    return $nextActor !== null;
                })
                ->schema([
                    Textarea::make('remarks')->required()->label('Remarks')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->forward($record, $data['remarks']);
                    Notification::make()->title('Claim forwarded.')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 3. Approve Action (Visible if Pending with Me AND NO Next Actor)
            Actions\Action::make('approve')
                ->label('Final Approval')
                ->color('success')
                ->icon('heroicon-m-check-badge')
                ->visible(function ($record, TourClaimWorkflow $workflow) {
                    // 1. Must be pending with me
                    if ($record->pending_with !== Auth::user()->employee?->employee_code) {
                        return false;
                    }

                    // 2. Check workflow: Is there a next person?
                    $nextActor = $workflow->determineNextActor($record, 'Forward', Auth::user());

                    // Show Approve ONLY if there is NO next actor (End of Chain)
                    return $nextActor === null;
                })
                ->requiresConfirmation()
                ->schema([
                    Textarea::make('remarks')->label('Approval Note')->default('Approved.')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->approve($record, $data['remarks']);
                    Notification::make()->title('Claim Approved!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 4. Raise Query Action (Always visible if Pending with Me)
            Actions\Action::make('query')
                ->label('Raise Query')
                ->color('warning')
                ->icon('heroicon-m-question-mark-circle')
                ->visible(fn($record) => $record->pending_with === Auth::user()->employee?->employee_code)
                ->schema([
                    Textarea::make('query')->required()->label('Query')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->query($record, $data['query']);
                    Notification::make()->title('Claim returned to employee with a query.')->warning()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Edit Action (Visible for Draft or Query)
            Actions\EditAction::make()
                ->visible(fn($record) => in_array($record->current_state, ['draft', 'query'])),
        ];
    }
}
