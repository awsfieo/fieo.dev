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
                    ->label('Appraisal Ref No')
                    ->searchable()
                    ->sortable(),
                // ->weight('bold'),

                TextColumn::make('employee.name')
                    ->label('Employee Name')
                    ->searchable()
                    ->sortable(),

                // Snapshot display: show designation captured at time of appraisal
                TextColumn::make('designation.designation')
                    ->label('Designation')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('appraisal_year')->label('Appraisal Year'),
                TextColumn::make('appraisal_cycle')->label('Appraisal Month'),

                TextColumn::make('status')
                    ->label('Current Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => ucfirst(str_replace('_', ' ', $state ?? 'draft')))
                    ->color(fn($record) => $record->status_color),

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
                    // Only show the Edit button if the status is 'draft'
                    ->visible(fn($record) => $record->status === 'draft'),
            ]);
    }
}
