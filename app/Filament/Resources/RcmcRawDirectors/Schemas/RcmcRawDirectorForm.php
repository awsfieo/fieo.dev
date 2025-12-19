<?php

namespace App\Filament\Resources\RcmcRawDirectors\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RcmcRawDirectorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Director Details')
                    ->schema([
                        TextInput::make('iec')
                            ->label('IEC Number')
                            ->readonly(),
                        TextInput::make('name')
                            ->label('Director Name')
                            ->readonly(),
                        TextInput::make('pan')
                            ->label('PAN')
                            ->readonly(),
                        TextInput::make('company_name')
                            ->label('Firm Name')
                            ->columnSpanFull()
                            ->readonly(),
                    ])->columns(3),

                Section::make('RCMC Information')
                    ->schema([
                        TextInput::make('file_number')
                            ->label('File Number')
                            ->readonly(),
                        TextInput::make('rcmc_number')
                            ->label('RCMC Number')
                            ->readonly(),
                        TextInput::make('rcmc_issue_date')
                            ->label('Issue Date')
                            ->readonly(),
                        TextInput::make('epc_short_name')
                            ->label('EPC Name')
                            ->readonly(),
                    ])->columns(2),

                Section::make('Additional Details')
                    ->schema([
                        TextInput::make('doe')
                            ->label('Date of Establishment')
                            ->readonly(),
                        TextInput::make('iec_issue_date')
                            ->label('IEC Issue Date')
                            ->readonly(),
                        TextInput::make('gstin')
                            ->label('GSTIN')
                            ->readonly(),
                        TextInput::make('nature_of_concern')
                            ->label('Nature of Concern')
                            ->readonly(),
                        TextInput::make('branch_code')
                            ->label('Branch Code')
                            ->readonly(),
                        TextInput::make('eou_sez')
                            ->label('EOU/SEZ Status')
                            ->readonly(),
                    ])->columns(3),
            ]);
    }
}