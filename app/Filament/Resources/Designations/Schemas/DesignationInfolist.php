<?php

namespace App\Filament\Resources\Designations\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DesignationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('sort_id')
                    ->numeric(),
                TextEntry::make('designation'),
                TextEntry::make('long_title')
                    ->placeholder('-'),
                TextEntry::make('short_title')
                    ->placeholder('-'),
                TextEntry::make('seniority')
                    ->numeric(),
                IconEntry::make('is_officer')
                    ->boolean(),
                IconEntry::make('is_active')
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
