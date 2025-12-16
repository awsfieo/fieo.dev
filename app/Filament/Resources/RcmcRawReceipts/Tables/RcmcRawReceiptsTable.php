<?php

namespace App\Filament\Resources\RcmcRawReceipts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\RcmcRawReceipt;

class RcmcRawReceiptsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('receipt_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),
                    
                TextColumn::make('receipt_number')
                    ->label('Receipt #')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('company_name')
                    ->label('Company')
                    ->searchable()
                    ->limit(30)
                    ->tooltip(fn (RcmcRawReceipt $record): string => $record->company_name ?? ''),

                TextColumn::make('gstin')
                    ->label('GSTIN')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('total_receipt_value')
                    ->label('Total Value')
                    ->money('INR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('office')
                    ->label('Office')
                    ->sortable()
                    ->badge(),

                TextColumn::make('voucher_type')
                    ->label('Type')
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
