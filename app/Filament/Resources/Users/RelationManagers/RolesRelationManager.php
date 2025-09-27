<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Roles\RoleResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Illuminate\Database\Eloquent\Builder;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $relatedResource = RoleResource::class;

    public function table(Table $table): Table
    {
        return $table
            // This makes the attach modal label records by their `name`:
            ->recordTitleAttribute('name')

            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('guard_name')
                    ->badge()
                    ->sortable(),
            ])

            // Header buttons (top-right of the relation table):
            ->headerActions([
                AttachAction::make()
                    ->modalHeading('Attach') // optional: 'Attach Roles' / 'Attach Permissions'
                    ->preloadRecordSelect()  // show options without typing
                    ->multiple()             // allow multi-select attach
                    ->recordSelectOptionsQuery(
                        fn(Builder $query) =>
                        $query->where('guard_name', 'web')->orderBy('name')
                    )
                    ->recordSelectSearchColumns(['name']),
            ])

            // Per-row actions:
            ->recordActions([
                DetachAction::make(),
            ])

            // Toolbar (where bulk actions now live):
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make(),
                ]),
            ]);
    }
}
