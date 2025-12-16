<?php

namespace App\Filament\Resources\RcmcRawApprovedApplications\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RcmcRawApprovedApplicationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('iec'),
                TextInput::make('company_name'),
                DatePicker::make('file_date'),
                TextInput::make('file_number'),
                TextInput::make('rcmc_number'),
                TextInput::make('application_type'),
                TextInput::make('status'),
                TextInput::make('closed_by'),
                TextInput::make('office'),
            ]);
    }
}
