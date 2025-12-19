<?php

namespace App\Filament\Resources\RcmcRawContactPersons\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RcmcRawContactPersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contact Details')
                    ->schema([
                        TextInput::make('iec')
                            ->label('IEC Number')
                            ->readonly(),
                        TextInput::make('name')
                            ->label('Name')
                            ->readonly(),
                        TextInput::make('contact_type')
                            ->label('Category')
                            ->readonly(),
                        TextInput::make('designation')
                            ->label('Designation')
                            ->readonly(),
                    ])->columns(2),

                Section::make('Communication')
                    ->schema([
                        TextInput::make('mobile')
                            ->label('Mobile')
                            ->readonly(),
                        TextInput::make('email')
                            ->label('Email')
                            ->readonly(),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->readonly(),
                    ])->columns(3),

                Section::make('Address & Location')
                    ->schema([
                        TextInput::make('city')
                            ->label('City')
                            ->readonly(),
                        TextInput::make('state')
                            ->label('State')
                            ->readonly(),
                        TextInput::make('pincode')
                            ->label('Pincode')
                            ->readonly(),
                    ])->columns(3),
            ]);
    }
}