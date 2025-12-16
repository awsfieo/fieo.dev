<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->orderByRaw('sort_id ASC NULLS LAST')->orderBy('name')
            )
            ->searchPlaceholder('eg. name: | supervisor:')
            ->columns([
                TextColumn::make('sort_id')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('user_id')
                //     ->numeric()
                //     ->sortable(),
                TextColumn::make('employee_code')
                    ->label('Emp Code')
                    ->sortable()
                    ->searchable(),
                // TextColumn::make('salutation')
                //     ->searchable(),
                TextColumn::make('display_name')
                    ->label('Display name')
                    ->state(fn($record) => trim(($record->salutation ?? '') . ' ' . ($record->name ?? '')))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        if (str_starts_with($search, 'supervisor:')) {
                            $term = trim(substr($search, 11));

                            return $query->whereHas(
                                'supervisor',
                                fn(Builder $q) =>
                                $q->where('name', 'ilike', "%{$term}%")
                            );
                        }

                        if (str_starts_with($search, 'name:')) {
                            $term = trim(substr($search, 5));

                            return $query->where('name', 'ilike', "%{$term}%");
                        }

                        // fallback: search both
                        return $query->where(function (Builder $q) use ($search) {
                            $q->where('name', 'ilike', "%{$search}%")
                                ->orWhereHas(
                                    'supervisor',
                                    fn(Builder $sq) =>
                                    $sq->where('name', 'ilike', "%{$search}%")
                                );
                        });
                    }),

                // TextColumn::make('gender')
                //     ->searchable(),
                // TextColumn::make('dob')
                //     ->date()
                //     ->sortable(),
                // TextColumn::make('doj')
                //     ->date()
                //     ->sortable(),
                TextColumn::make('designation.designation')
                    ->label('Designation')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('department.department')
                    ->label('Department')
                    ->numeric()
                    ->sortable(),
                // TextColumn::make('office.office')
                //     ->numeric()
                //     ->sortable(),
                // TextColumn::make('status')
                //     ->sortable()
                //     ->searchable(),
                // TextColumn::make('basic')
                //     ->numeric()
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('supervisor_display_name')
                    ->label('Supervisor')
                    ->state(
                        fn($record) => $record->supervisor
                            ? trim(($record->supervisor->salutation ?? '') . ' ' . ($record->supervisor->name ?? ''))
                            : null
                    )
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        if (str_starts_with($search, 'supervisor:')) {
                            $term = trim(substr($search, 11));

                            return $query->whereHas(
                                'supervisor',
                                fn(Builder $q) =>
                                $q->where('name', 'ilike', "%{$term}%")
                            );
                        }

                        if (str_starts_with($search, 'name:')) {
                            $term = trim(substr($search, 5));

                            return $query->where('name', 'ilike', "%{$term}%");
                        }

                        // fallback: search both
                        return $query->where(function (Builder $q) use ($search) {
                            $q->where('name', 'ilike', "%{$search}%")
                                ->orWhereHas(
                                    'supervisor',
                                    fn(Builder $sq) =>
                                    $sq->where('name', 'ilike', "%{$search}%")
                                );
                        });
                    }),


                // TextColumn::make('manager.display_name')
                //     ->searchable(),
                // TextColumn::make('approver.display_name')
                //     ->label('Approver')
                //     ->sortable()
                //     ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('mobile')
                    ->searchable(),
                // TextColumn::make('pan')
                //     ->searchable(),
                // TextColumn::make('aadhar')
                //     ->searchable(),
                // TextColumn::make('uan')
                //     ->searchable(),
                // TextColumn::make('lic_id')
                //     ->searchable(),
                IconColumn::make('is_active')
                    ->boolean(),
                // TextColumn::make('created_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
                // TextColumn::make('updated_at')
                //     ->dateTime()
                //     ->sortable()
                //     ->toggleable(isToggledHiddenByDefault: true),
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
