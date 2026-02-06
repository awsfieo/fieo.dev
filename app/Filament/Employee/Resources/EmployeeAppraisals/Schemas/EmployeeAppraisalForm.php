<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;

class EmployeeAppraisalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Increment Order Details')
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

                        Select::make('status')
                            ->label('Appraisal Status')
                            ->disabled()
                            ->dehydrated(false)
                            ->options([
                                'Pending' => 'Pending',
                                'Processed' => 'Processed',
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
                                'Yes' => 'Yes',
                                'No' => 'No',
                            ])
                            ->default('No'),

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
