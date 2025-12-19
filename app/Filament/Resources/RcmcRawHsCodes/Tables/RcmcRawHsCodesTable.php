<?php

namespace App\Filament\Resources\RcmcRawHsCodes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Models\RcmcRawHsCode;

class RcmcRawHsCodesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iec')
                    ->label('IEC')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('hs_code')
                    ->label('HS Code')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('product_description')
                    ->label('Product')
                    ->limit(50)
                    ->tooltip(fn (RcmcRawHsCode $record): string => $record->product_description ?? '')
                    ->searchable(),

                TextColumn::make('export_type')
                    ->label('Type')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('rcmc_number')
                    ->label('RCMC No')
                    ->searchable(),

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
