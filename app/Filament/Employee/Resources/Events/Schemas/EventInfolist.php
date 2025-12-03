<?php

namespace App\Filament\Employee\Resources\Events\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('title'),
                TextEntry::make('slug'),
                TextEntry::make('excerpt')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('start_at')
                    ->dateTime(),
                TextEntry::make('end_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('timezone'),
                IconEntry::make('add_home_page_ticker')
                    ->boolean(),
                TextEntry::make('listing_start_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('listing_end_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('venue_name')
                    ->placeholder('-'),
                TextEntry::make('venue_city')
                    ->placeholder('-'),
                TextEntry::make('venue_country')
                    ->placeholder('-'),
                TextEntry::make('event_type'),
                TextEntry::make('event_mode'),
                IconEntry::make('under_mai_scheme')
                    ->boolean(),
                TextEntry::make('capacity')
                    ->numeric()
                    ->placeholder('-'),
                IconEntry::make('allow_registration')
                    ->boolean(),
                IconEntry::make('allow_partial_payment')
                    ->boolean(),
                TextEntry::make('applicable_gst')
                    ->numeric(),
                IconEntry::make('tds_deducted')
                    ->boolean(),
                TextEntry::make('tds_percentage')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('employee_code')
                    ->placeholder('-'),
                TextEntry::make('designation_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('department_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('office_id')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('status'),
                TextEntry::make('current_state'),
                TextEntry::make('published_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('created_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('updated_by')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
