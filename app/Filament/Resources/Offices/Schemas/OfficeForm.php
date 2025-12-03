<?php

namespace App\Filament\Resources\Offices\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class OfficeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('sort_id')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('office')
                    ->required(),
                Select::make('type')
                    ->required()
                    ->options(['HO' => 'Head Office', 'RO' => 'Regional Office', 'CO' => 'Chapter Office'])
                    ->native(false),
                TextArea::make('address')
                    ->columnSpan(2),
                TextInput::make('city'),
                TextInput::make('state'),
                TextInput::make('pin'),
                TextInput::make('country')
                    ->required()
                    ->default('India'),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                TextInput::make('fax'),
                TextInput::make('latitude')
                    ->numeric(),
                TextInput::make('longitude')
                    ->numeric(),
                Select::make('parent_id')
                    ->label('Parent office')
                    ->relationship(
                        name: 'parent',
                        titleAttribute: 'office',
                        modifyQueryUsing: function ($query, $get, ?\App\Models\Office $record) {
                            if ($country = $get('country')) $query->where('country', $country);
                            if ($record?->id) $query->whereKeyNot($record->id);
                        },
                    )
                    ->searchable()
                    ->preload()
                    ->native(false)
                    ->placeholder('— None —')
                    ->nullable(),
                Toggle::make('is_active')
                    ->required(),
            ])->columns(3);
    }
}
