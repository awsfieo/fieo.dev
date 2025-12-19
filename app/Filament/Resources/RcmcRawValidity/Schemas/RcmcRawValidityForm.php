<?php

namespace App\Filament\Resources\RcmcRawValidity\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RcmcRawValidityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Basic Details')
                    ->schema([
                        TextInput::make('iec')
                            ->label('IEC Number')
                            ->readonly(),
                        TextInput::make('rcmc_number')
                            ->label('RCMC Number')
                            ->readonly(),
                        TextInput::make('file_number')
                            ->label('File Number')
                            ->readonly(),
                        TextInput::make('epc_short_name')
                            ->label('EPC')
                            ->readonly(),
                    ])->columns(2),

                Section::make('Status & Dates')
                    ->schema([
                        TextInput::make('application_status')
                            ->label('Status')
                            ->readonly(),
                        TextInput::make('msme_status')
                            ->label('MSME Status')
                            ->readonly(),
                        TextInput::make('star_rating')
                            ->label('Star Rating')
                            ->readonly(),
                        TextInput::make('rcmc_issue_date')
                            ->label('Issue Date')
                            ->readonly(),
                        TextInput::make('rcmc_valid_upto')
                            ->label('Valid Upto')
                            ->readonly(),
                    ])->columns(3),

                Section::make('Financials')
                    ->schema([
                        TextInput::make('annual_turnover')
                            ->label('Annual Turnover')
                            ->readonly(),
                        TextInput::make('export_turnover')
                            ->label('Export Turnover')
                            ->readonly(),
                        TextInput::make('export_performance')
                            ->label('Concerned Product Performance')
                            ->columnSpanFull()
                            ->readonly(),
                    ])->columns(2),
            ]);
    }
}