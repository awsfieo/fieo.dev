<?php

namespace App\Filament\Resources\RcmcRawValidity\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Table;

class RcmcRawValidityTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iec')
                    ->label('IEC')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rcmc_number')
                    ->label('RCMC No')
                    ->searchable(),

                TextColumn::make('star_rating')
                    ->label('Star Rating')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('annual_turnover')
                    ->label('Annual T/O')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('export_turnover')
                    ->label('Export T/O')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('rcmc_valid_upto')
                    ->label('Valid Upto')
                    ->date()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
