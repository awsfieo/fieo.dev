<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class EmployeeAppraisalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('employee_code')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('appraisal_year')
                    ->sortable()
                    ->label('Year'),

                TextColumn::make('appraisal_month')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'April' => 'info',
                        'October' => 'warning',
                        default => 'gray',
                    }),

                // --- NEW: Workflow Status (From Related Appraisal) ---
                TextColumn::make('appraisal.status')
                    ->label('Form Status')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state ?? 'Not Started')))
                    ->color(fn($state) => match ($state) {
                        'draft'                        => 'gray',
                        'submitted'                    => 'info',
                        'evaluation_pending'           => 'warning',
                        'regional_head_review_pending' => 'orange',
                        'final_assessment_pending'     => 'primary',
                        'completed', 'closed'          => 'success',
                        default                        => 'gray',
                    })
                    ->sortable(),

                // --- NEW: Pending With (From Related Appraisal) ---
                TextColumn::make('appraisal_pending_with')
                    ->label('Pending With')
                    ->state(fn ($record) => $record->appraisal?->pendingWith 
                        ? trim(($record->appraisal->pendingWith->salutation ?? '') . ' ' . ($record->appraisal->pendingWith->name ?? '')) 
                        : '-')
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(),

                TextColumn::make('status')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'Pending' => 'danger',
                        'Processed' => 'success',
                        'Hold' => 'warning',
                        default => 'gray',
                    }),
                IconColumn::make('increment_granted')
                    ->boolean()
                    ->label('Increment Granted')
                    ->alignCenter()
                    ->sortable(),

                TextColumn::make('increment_percentage')
                    ->label('Increment %')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        // Logic: If user is HOD Personnel AND status is NOT Released, show 'TBD'
                        if (Auth::user()->hasRole('HOD Personnel') && $record->status !== 'Released') {
                            return 'TBD';
                        }
                        return $state;
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'TBD' => 'gray',
                        default => 'success',
                    }),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Last Updated')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Processed' => 'Processed',
                        'Hold' => 'Hold',
                        'Released' => 'Released',
                    ]),
                SelectFilter::make('appraisal_year')
                    ->label('Appraisal Year'),
                SelectFilter::make('appraisal_month')
                    ->label('Appraisal Month')
                    ->options(['April' => 'April', 'October' => 'October']),

            ])
            ->recordActions([
                EditAction::make(),
                // --- NEW: DG & CEO Revert Action ---
                Action::make('revert_appraisal')
                    ->label('Revert to Review')
                    ->icon('heroicon-m-arrow-path')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Revert Appraisal to Final Review')
                    ->modalDescription('This will reopen the appraisal, set the status back to "Final Assessment Pending", and assign it to you. Do you want to proceed?')
                    ->visible(
                        fn($record) =>
                        Auth::user()->hasRole('DG & CEO') &&
                            $record->appraisal &&
                            in_array($record->appraisal->status, ['completed', 'closed'])
                    )
                    ->action(function ($record) {
                        // 1. Update the related Appraisal Workflow
                        $record->appraisal->update([
                            'status'       => 'final_assessment_pending',
                            'pending_with' => Auth::user()->employee?->employee_code,
                        ]);

                        // 2. Optional: Reset HR status if it was processed
                        // $record->update(['status' => 'Pending']); 

                        Notification::make()
                            ->title('Appraisal Reverted')
                            ->body('The appraisal has been reopened for final assessment.')
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
