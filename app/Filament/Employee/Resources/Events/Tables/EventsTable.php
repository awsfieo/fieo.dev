<?php

namespace App\Filament\Employee\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('slug')
                    ->searchable(),
                TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('timezone')
                    ->searchable(),
                IconColumn::make('add_home_page_ticker')
                    ->boolean(),
                TextColumn::make('listing_start_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('listing_end_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('venue_name')
                    ->searchable(),
                TextColumn::make('venue_city')
                    ->searchable(),
                TextColumn::make('venue_country')
                    ->searchable(),
                TextColumn::make('event_type')
                    ->searchable(),
                TextColumn::make('event_mode')
                    ->searchable(),
                IconColumn::make('under_mai_scheme')
                    ->boolean(),
                TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('allow_registration')
                    ->boolean(),
                IconColumn::make('allow_partial_payment')
                    ->boolean(),
                TextColumn::make('applicable_gst')
                    ->numeric()
                    ->sortable(),
                IconColumn::make('tds_deducted')
                    ->boolean(),
                TextColumn::make('tds_percentage')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('employee_code')
                    ->searchable(),
                TextColumn::make('designation_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('department_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('office_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('current_state')
                    ->searchable(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
