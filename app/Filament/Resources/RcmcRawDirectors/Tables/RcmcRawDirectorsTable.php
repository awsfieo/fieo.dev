<?php

namespace App\Filament\Resources\RcmcRawDirectors\Tables;

use App\Models\RcmcRawDirector;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\ViewAction;

class RcmcRawDirectorsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iec')
                    ->label('IEC')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('name')
                    ->label('Director Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->label('Firm Name')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (RcmcRawDirector $record): string => $record->company_name ?? ''),

                TextColumn::make('pan')
                    ->label('PAN')
                    ->searchable(),

                TextColumn::make('rcmc_number')
                    ->label('RCMC No')
                    ->searchable(),

                TextColumn::make('file_number')
                    ->label('File No')
                    ->searchable(),

                TextColumn::make('doe')
                    ->label('Est. Date')
                    ->toggleable(isToggledHiddenByDefault: true),

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