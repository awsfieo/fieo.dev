<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;

class EmployeeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sort_id')
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                TextInput::make('employee_code')
                    ->required(),
                Select::make('salutation')
                    ->label('Salutation')
                    ->options([
                        'Mr'  => 'Mr',
                        'Mrs' => 'Mrs',
                        'Ms'  => 'Ms',
                        'Shri'  => 'Shri',
                        'Smt'  => 'Smt',
                        'Dr'  => 'Dr',
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('name')
                    ->required(),
                Select::make('gender')
                    ->options([
                        'Male'   => 'Male',
                        'Female' => 'Female',
                    ])
                    ->native(false),
                DatePicker::make('dob'),
                DatePicker::make('doj'),

                Select::make('designation_id')
                    ->label('Designation')
                    ->options(\App\Models\Designation::query()->orderBy('sort_id')->pluck('designation', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                Select::make('department_id')
                    ->label('Department')
                    ->options(\App\Models\Department::query()->orderBy('sort_id')->pluck('department', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                Select::make('office_id')
                    ->label('Office')
                    ->options(\App\Models\Office::query()->orderBy('sort_id')->pluck('office', 'id'))
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),
                Select::make('status')
                    ->options([
                        'confirmed'   => 'Confirmed',
                        'contractual' => 'Contractual',
                        'probation'   => 'Probation',
                    ])
                    ->default('confirmed')
                    ->required()
                    ->native(false),
                TextInput::make('basic'),
                Select::make('supervisor_code')
                    ->label('Supervisor')
                    ->options(\App\Models\Employee::query()->orderBy('name')->pluck('name', 'employee_code'))
                    ->searchable()
                    ->preload()
                    ->native(false),

                Select::make('appraisal_month')
                    ->options([
                        'April' => 'April',
                        'October' => 'October',
                    ])
                    ->default('April'),

                Select::make('approver_code')
                    ->label('Approver')
                    ->options(\App\Models\Employee::query()->orderBy('name')->pluck('name', 'employee_code'))
                    ->searchable()
                    ->preload()
                    ->native(false),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required(),
                TextInput::make('mobile'),
                TextInput::make('phone'),
                TextInput::make('pan'),
                TextInput::make('aadhar'),
                TextInput::make('uan'),
                TextInput::make('lic_id'),
                TextInput::make('bank_account'),
                Toggle::make('is_active')
                    ->required(),
            ])->columns(4);
    }
}
