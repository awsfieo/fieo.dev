<?php

namespace App\Filament\Employee\Resources\Events;

use App\Filament\Employee\Resources\Events\Pages\CreateEvent;
use App\Filament\Employee\Resources\Events\Pages\EditEvent;
use App\Filament\Employee\Resources\Events\Pages\ListEvents;
use App\Filament\Employee\Resources\Events\Pages\ViewEvent;
use App\Filament\Employee\Resources\Events\Schemas\EventForm;
use App\Filament\Employee\Resources\Events\Schemas\EventInfolist;
use App\Filament\Employee\Resources\Events\Tables\EventsTable;
use App\Models\Event;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static string|UnitEnum|null $navigationGroup = 'Event Management';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CalendarDays;

    protected static ?string $recordTitleAttribute = 'slug';

    public static function form(Schema $schema): Schema
    {
        return EventForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EventInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEvents::route('/'),
            'create' => CreateEvent::route('/create'),
            'view' => ViewEvent::route('/{record}'),
            'edit' => EditEvent::route('/{record}/edit'),
        ];
    }
}
