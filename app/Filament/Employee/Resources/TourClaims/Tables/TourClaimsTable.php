<?php

namespace App\Filament\Employee\Resources\TourClaims\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;

class TourClaimsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                // 1. Application No
                TextColumn::make('application_no')
                    ->label('Application No')
                    ->sortable()
                    ->searchable(),

                // 2. Tour Dates (using start date only for now)
                TextColumn::make('tour_start_date')
                    ->label('Tour Dates')
                    ->date()
                    ->sortable(),

                // 3. Status
                TextColumn::make('current_state')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                
                EditAction::make()
                    ->visible(fn ($record) => in_array($record->current_state, ['draft', 'query'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
