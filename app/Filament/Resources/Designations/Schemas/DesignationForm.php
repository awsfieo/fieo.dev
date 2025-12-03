<?php

namespace App\Filament\Resources\Designations\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class DesignationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sort_id')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('designation')
                    ->required(),
                TextInput::make('long_title'),
                TextInput::make('short_title'),
                TextInput::make('seniority')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('is_officer')
                    ->required(),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
