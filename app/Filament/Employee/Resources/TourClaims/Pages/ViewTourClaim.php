<?php

namespace App\Filament\Employee\Resources\TourClaims\Pages;

use App\Filament\Employee\Resources\TourClaims\TourClaimResource;
use App\Services\TourClaimWorkflow;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class ViewTourClaim extends ViewRecord
{
    protected static string $resource = TourClaimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // 1. Submit Action (Visible for Drafts)
            Actions\Action::make('submit')
                ->label('Submit for Review')
                ->color('primary')
                ->icon('heroicon-m-paper-airplane')
                ->visible(fn ($record) => 
                    $record->current_state === 'draft' && 
                    $record->employee_id === Auth::user()->employee?->id
                )
                ->requiresConfirmation()
                ->action(function ($record, TourClaimWorkflow $workflow) {
                    $workflow->submit($record);
                    Notification::make()->title('Claim submitted successfully!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 1b. Reply to Query (Visible for Query state)
            Actions\Action::make('reply')
                ->label('Reply to Query')
                ->color('primary')
                ->icon('heroicon-m-chat-bubble-left-right')
                ->visible(fn ($record) => 
                    $record->current_state === 'query' && 
                    $record->employee_id === Auth::user()->employee?->id
                )
                ->requiresConfirmation()
                ->modalHeading('Reply to Query')
                ->modalDescription('Please provide your explanation or corrections regarding the query.')
                ->form([
                    Textarea::make('reply_note')
                        ->label('Reply / Remarks')
                        ->required()
                        ->rows(3)
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->replyToQuery($record, $data['reply_note']);
                    Notification::make()->title('Reply submitted successfully!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 2. Forward Action (Reviewer)
            Actions\Action::make('forward')
                ->label('Recommend / Forward')
                ->color('success')
                ->icon('heroicon-m-arrow-right-circle')
                ->visible(function ($record, TourClaimWorkflow $workflow) {
                    // Must be pending with me
                    if ($record->pending_with !== Auth::user()->employee?->employee_code) {
                        return false;
                    }
                    // Must NOT be in settlement phase
                    if ($record->current_state === 'approved') {
                        return false; 
                    }

                    // Check workflow: Is there a next person?
                    $nextActor = $workflow->determineNextActor($record, 'Forward', Auth::user());
                    return $nextActor !== null;
                })
                ->form([
                    Textarea::make('remarks')->required()->label('Remarks')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->forward($record, $data['remarks']);
                    Notification::make()->title('Claim forwarded.')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 3. Approve Action (Final Authority)
            Actions\Action::make('approve')
                ->label('Final Approval')
                ->color('success')
                ->icon('heroicon-m-check-badge')
                ->visible(function ($record, TourClaimWorkflow $workflow) {
                    if ($record->pending_with !== Auth::user()->employee?->employee_code) {
                        return false;
                    }
                    if ($record->current_state === 'approved') {
                        return false; 
                    }

                    // Check workflow: If NO next actor, show Approve
                    $nextActor = $workflow->determineNextActor($record, 'Forward', Auth::user());
                    return $nextActor === null;
                })
                ->requiresConfirmation()
                ->form([
                    Textarea::make('remarks')->label('Approval Note')->default('Approved.')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->approve($record, $data['remarks']);
                    Notification::make()->title('Claim Approved!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 4. Raise Query Action (For Reviewers/Approvers)
            Actions\Action::make('query')
                ->label('Raise Query')
                ->color('warning')
                ->icon('heroicon-m-question-mark-circle')
                ->visible(fn ($record) => 
                    $record->pending_with === Auth::user()->employee?->employee_code &&
                    $record->current_state !== 'approved' // Hide during settlement
                )
                ->form([
                    Textarea::make('query')->required()->label('Query')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->query($record, $data['query']);
                    Notification::make()->title('Claim returned with query.')->warning()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // 5. Settlement Action (Accounts Executive - Final Step)
            Actions\Action::make('settle')
                ->label('Settlement Details')
                ->color('success')
                ->icon('heroicon-m-currency-rupee')
                ->visible(fn ($record) => 
                    $record->current_state === 'approved' && 
                    $record->pending_with === Auth::user()->employee?->employee_code
                )
                ->form([
                    DatePicker::make('date')
                        ->label('Payment Date')
                        ->required()
                        ->default(now()),
                    
                    TextInput::make('amount')
                        ->label('Settlement Amount')
                        ->numeric()
                        ->prefix('₹')
                        ->required()
                        ->default(fn ($record) => $record->amount_reimburse_inr),
                    
                    TextInput::make('utr')
                        ->label('UTR / Cheque No / Reference')
                        ->required()
                        ->maxLength(50),

                    Textarea::make('remarks')
                        ->label('Payment Note')
                        ->default('Payment Released.')
                ])
                ->action(function ($record, array $data, TourClaimWorkflow $workflow) {
                    $workflow->settle($record, $data);
                    
                    Notification::make()->title('Claim Settled & Closed!')->success()->send();
                    $this->redirect($this->getResource()::getUrl('index'));
                }),

            // Edit Action (For Employee adjustments)
            Actions\EditAction::make()
                ->visible(fn ($record) => in_array($record->current_state, ['draft', 'query'])),
        ];
    }
}