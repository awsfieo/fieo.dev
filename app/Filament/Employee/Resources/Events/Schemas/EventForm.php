<?php

namespace App\Filament\Employee\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('excerpt')
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->columnSpanFull(),
                DateTimePicker::make('start_at')
                    ->required(),
                DateTimePicker::make('end_at'),
                TextInput::make('timezone')
                    ->required()
                    ->default('Asia/Kolkata'),
                Toggle::make('add_home_page_ticker')
                    ->required(),
                DateTimePicker::make('listing_start_at'),
                DateTimePicker::make('listing_end_at'),
                TextInput::make('venue_name'),
                TextInput::make('venue_city'),
                TextInput::make('venue_country'),
                TextInput::make('event_type')
                    ->required()
                    ->default('domestic'),
                TextInput::make('event_mode')
                    ->required()
                    ->default('offline'),
                Toggle::make('under_mai_scheme')
                    ->required(),
                TextInput::make('capacity')
                    ->numeric(),
                Toggle::make('allow_registration')
                    ->required(),
                Toggle::make('allow_partial_payment')
                    ->required(),
                TextInput::make('applicable_gst')
                    ->required()
                    ->numeric()
                    ->default(0),
                Toggle::make('tds_deducted')
                    ->required(),
                TextInput::make('tds_percentage')
                    ->numeric(),
                TextInput::make('registration_charges_json'),
                TextInput::make('employee_code'),
                TextInput::make('designation_id')
                    ->numeric(),
                TextInput::make('department_id')
                    ->numeric(),
                TextInput::make('office_id')
                    ->numeric(),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('current_state')
                    ->required()
                    ->default('draft'),
                DateTimePicker::make('published_at'),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
