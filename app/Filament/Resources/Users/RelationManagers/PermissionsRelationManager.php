<?php

namespace App\Filament\Resources\Users\RelationManagers;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\BulkActionGroup;
use Illuminate\Database\Eloquent\Builder;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $relatedResource = PermissionResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->modifyQueryUsing(fn(Builder $query) => $query->where('guard_name', 'web'))
            ->columns([
                TextColumn::make('name')
                    ->label('Permission')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->badge(),
            ])
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
            ->recordActions([
                DetachAction::make()
                    ->modalHeading('Remove permission')
                    ->successNotificationTitle('Permission removed'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()
                        ->successNotificationTitle('Permissions removed')
                        ->chunkSelectedRecords(250),
                ]),
            ]);
    }
}
