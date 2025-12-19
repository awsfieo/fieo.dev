<?php

namespace App\Filament\Resources\RcmcRawHsCodes\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class RcmcRawHsCodeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Exporter Details')
                    ->schema([
                        TextInput::make('iec')
                            ->label('IEC Number')
                            ->readonly(),
                        TextInput::make('company_name')
                            ->label('Company Name')
                            ->readonly(),
                        TextInput::make('rcmc_number')
                            ->label('RCMC Number')
                            ->readonly(),
                    ])->columns(3),

                Section::make('Product Information')
                    ->schema([
                        TextInput::make('hs_code')
                            ->label('HS Code')
                            ->readonly(),
                        TextInput::make('export_type')
                            ->label('Export Type')
                            ->readonly(),
                        Textarea::make('product_description')
                            ->label('Product Description')
                            ->rows(3)
                            ->columnSpanFull()
                            ->readonly(),
                        Textarea::make('general_description')
                            ->label('Sector/General Description')
                            ->rows(2)
                            ->columnSpanFull()
                            ->readonly(),
                    ])->columns(2),
            ]);
    }
}