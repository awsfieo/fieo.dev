<?php

namespace App\Filament\Resources\Departments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;

class DepartmentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->orderByRaw('sort_id ASC NULLS LAST')->orderBy('department')
            )
            ->columns([
                TextColumn::make('sort_id')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('department')
                    ->searchable(),
                TextColumn::make('long_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('short_title')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'HO' => 'Head Office',
                        'DO' => 'Department',
                        'RO' => 'Regional Office',
                        'CO' => 'Chapter Office',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                    TextColumn::make('region')
                    ->label('Region')
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'HO' => 'Head Office',
                        'NR' => 'Northern Region',
                        'ER' => 'Eastern Region',
                        'WR' => 'Western Region',
                        'SR' => 'Southern Region',
                        default => $state,
                    })
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('gstin')
                    ->label('GSTIN')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('mid')
                    ->label('MID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('url')
                    ->label('URL')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('parent.department')
                    ->label('Parent')
                    ->default('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('office.office')
                    ->label('Office')
                    ->default('—')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->boolean()
                    ->toggleable(),
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
                SelectFilter::make('type')
                    ->options([
                        'HO' => 'Head Office',
                        'DO' => 'Department',
                        'RO' => 'Regional Office',
                        'CO' => 'Chapter Office',
                    ]),

                SelectFilter::make('parent_id')
                    ->label('Parent Dept')
                    ->relationship('parent', 'department')   // Department->parent()->department
                    ->searchable()
                    ->preload(),

                SelectFilter::make('office_id')
                    ->label('Office')
                    ->relationship('office', 'office')       // Department->office()->office
                    ->searchable()
                    ->preload(),
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
