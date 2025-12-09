<?php

namespace App\Filament\Employee\Resources\Appraisals\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AppraisalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('application_no'),
                TextEntry::make('employee.name')
                    ->label('Employee'),
                TextEntry::make('employee_code'),
                TextEntry::make('designation.id')
                    ->label('Designation')
                    ->placeholder('-'),
                TextEntry::make('department.id')
                    ->label('Department')
                    ->placeholder('-'),
                TextEntry::make('office.id')
                    ->label('Office')
                    ->placeholder('-'),
                TextEntry::make('basic')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('appraisal_year')
                    ->numeric(),
                TextEntry::make('appraisal_cycle'),
                TextEntry::make('status'),
                TextEntry::make('pending_with')
                    ->placeholder('-'),
                TextEntry::make('appraisal_start_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('appraisal_end_date')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('deadline_extension')
                    ->date()
                    ->placeholder('-'),
                TextEntry::make('appraisal_form_data')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('common_evaluation_data')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('regional_head_assessment_data')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('final_assessment_data')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('final_increment')
                    ->placeholder('-')
                    ->columnSpanFull(),
                IconEntry::make('is_released')
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
