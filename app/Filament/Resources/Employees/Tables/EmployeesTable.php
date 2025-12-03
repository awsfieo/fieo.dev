<?php

namespace App\Filament\Resources\Employees\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn($query) =>
                $query->orderByRaw('sort_id ASC NULLS LAST')->orderBy('name')
            )
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
                    ->searchable(),
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
                TextColumn::make('supervisor.display_name')
                    ->label('Supervisor')
                    ->sortable()
                    ->searchable(),
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
