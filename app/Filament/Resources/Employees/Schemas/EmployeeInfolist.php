<?php

namespace App\Filament\Resources\Employees\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EmployeeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sort_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('user_id')
                    ->numeric(),
                TextEntry::make('employee_code'),
                TextEntry::make('salutation')
                    ->placeholder('-'),
                TextEntry::make('name'),
                TextEntry::make('gender')
                    ->placeholder('-'),
                TextEntry::make('dob')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('doj')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('designation_id')
                    ->numeric(),
                TextEntry::make('department_id')
                    ->numeric(),
                TextEntry::make('office_id')
                    ->numeric(),
                TextEntry::make('status'),
                TextEntry::make('basic')
                    ->placeholder('-'),
                TextEntry::make('supervisor_code')
                    ->placeholder('-'),
                TextEntry::make('manager_code')
                    ->placeholder('-'),
                TextEntry::make('approver_code')
                    ->placeholder('-'),
                TextEntry::make('email')
                    ->label('Email address'),
                TextEntry::make('mobile')
                    ->placeholder('-'),
                TextEntry::make('pan')
                    ->placeholder('-'),
                TextEntry::make('aadhar')
                    ->placeholder('-'),
                TextEntry::make('uan')
                    ->placeholder('-'),
                TextEntry::make('lic_id')
                    ->placeholder('-'),
                IconEntry::make('is_active')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
