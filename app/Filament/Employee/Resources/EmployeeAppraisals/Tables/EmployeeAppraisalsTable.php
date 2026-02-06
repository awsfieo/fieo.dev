<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;

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
                    ->color(fn (string $state): string => match ($state) {
                        'April' => 'info',
                        'October' => 'warning',
                        default => 'gray',
                    }),

                IconColumn::make('increment_granted')
                    ->boolean()
                    ->label('Approved')
                    ->alignCenter(),

                TextColumn::make('increment_percentage')
                    ->badge()
                    ->color('success')
                    ->label('Increment'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending' => 'danger',
                        'Processed' => 'success',
                        'Hold' => 'warning',
                        default => 'gray',
                    }),
                
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->label('Last Updated')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'Pending' => 'Pending',
                        'Processed' => 'Processed',
                        'Hold' => 'Hold',
                    ]),
                SelectFilter::make('appraisal_year')
                    ->label('Year'),
                SelectFilter::make('appraisal_month')
                    ->label('Cycle')
                    ->options(['April' => 'April', 'October' => 'October']),
          
            ])
            ->filters([
                //
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
