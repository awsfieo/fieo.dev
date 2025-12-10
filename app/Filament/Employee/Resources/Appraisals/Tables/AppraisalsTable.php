<?php

namespace App\Filament\Employee\Resources\Appraisals\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use Illuminate\Support\Facades\Auth;

class AppraisalsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('application_no')
                    ->searchable()
                    ->sortable(),
                    // ->weight('bold'),

                TextColumn::make('employee.name')
                    ->label('Employee')
                    ->searchable()
                    ->sortable(),

                // Snapshot display: show designation captured at time of appraisal
                TextColumn::make('designation.designation')
                    ->label('Designation (Snapshot)')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('appraisal_year')->label('Year'),
                TextColumn::make('appraisal_cycle')->label('Cycle'),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($record) => $record->status_color),

                // TextColumn::make('pendingWith.name')
                //     ->label('Pending With')
                //     ->icon('heroicon-m-user'),
            ])
            ->filters([
                // Add year filter later if needed
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make()
                    ->label(fn($record) => $record->status === 'draft' ? 'Edit' : 'Edit')
                    ->color(fn($record) => $record->status === 'draft' ? 'primary' : 'warning'),
            ]);
    }
}