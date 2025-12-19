<?php

namespace App\Filament\Resources\RcmcRawContactPersons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RcmcRawContactPersonTable
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
                    ->label('Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contact_type')
                    ->label('Type')
                    ->badge()
                    ->searchable(),

                TextColumn::make('designation')
                    ->label('Designation')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mobile')
                    ->label('Mobile')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
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
