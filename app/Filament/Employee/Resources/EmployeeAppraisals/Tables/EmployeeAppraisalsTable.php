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
                    ->color(fn (string $state): string => match ($state) {
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
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
