<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;

class EmployeeAppraisalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Appraisal Configuration')
                    ->schema([
                        TextInput::make('employee_code')
                            ->label('Employee Code')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('name')
                            ->label('Employee Name')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('appraisal_year')
                            ->label('Appraisal Year')
                            ->disabled()
                            ->dehydrated(false),

                        Select::make('appraisal_month')
                            ->label('Appraisal Month')
                            ->dehydrated()
                            ->options([
                                'April' => 'April',
                                'October' => 'October',
                            ])
                            ->default('April'),

                        DatePicker::make('appraisal_start_date')
                            ->label('Start Date')
                            ->native(false),

                        DatePicker::make('appraisal_end_date')
                            ->label('End Date')
                            ->native(false),

                        Toggle::make('deadline_extension')
                            ->label('Grant Deadline Extension')
                            ->onColor('warning')
                            ->live(), // Ensures the date field below shows/hides instantly

                        DatePicker::make('deadline_extension_date')
                            ->label('Extended Deadline')
                            ->native(false)
                            ->visible(fn($get) => $get('deadline_extension')) // Only show if toggle is ON
                            ->required(fn($get) => $get('deadline_extension')),
                    ])
                    ->columns(2),

                // --- NEW SECTION: DATES & EXTENSIONS ---
                Section::make('Appraisal Status')
                    ->schema([
                        Select::make('status')
                            ->label('Appraisal Status')
                            ->disabled()
                            ->dehydrated(false)
                            ->options([
                                'Pending' => 'Pending',
                                'Processed' => 'Processed',
                                'Released' => 'Released',
                                'Hold' => 'Hold',
                            ])
                            ->default('Pending')
                            ->required()
                            ->native(false),

                        Select::make('increment_granted')
                            ->label('Increment Granted')
                            ->disabled()
                            ->dehydrated(false)
                            ->options([
                                '1' => 'Yes',
                                '0' => 'No',
                            ])
                            ->formatStateUsing(fn ($state) => (string) (int) $state)
                            ->default('0'),

                        TextInput::make('increment_percentage')
                            ->label('Increment Percent')
                            ->suffix('%')
                            ->disabled()
                            ->dehydrated(false)
                            ->extraInputAttributes(['style' => 'font-weight: 800; color: #16a34a;']),

                    ])
                    ->columns(2),
            ]);
    }
}
