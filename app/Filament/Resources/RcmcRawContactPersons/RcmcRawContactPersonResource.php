<?php

namespace App\Filament\Resources\RcmcRawContactPersons;

use App\Filament\Resources\RcmcRawContactPersons\Pages\CreateRcmcRawContactPerson;
use App\Filament\Resources\RcmcRawContactPersons\Pages\EditRcmcRawContactPerson;
use App\Filament\Resources\RcmcRawContactPersons\Pages\ListRcmcRawContactPersons;
use App\Filament\Resources\RcmcRawContactPersons\Schemas\RcmcRawContactPersonForm;
use App\Filament\Resources\RcmcRawContactPersons\Tables\RcmcRawContactPersonTable;
use App\Models\RcmcRawContactPerson;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RcmcRawContactPersonResource extends Resource
{
    protected static ?string $model = RcmcRawContactPerson::class;

    protected static string|UnitEnum|null $navigationGroup = 'Upload RCMC Data';

    protected static ?string $navigationLabel = 'Contact Persons';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    public static function form(Schema $schema): Schema
    {
        return RcmcRawContactPersonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RcmcRawContactPersonTable::configure($table);
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
            'index' => ListRcmcRawContactPersons::route('/'),
            'create' => CreateRcmcRawContactPerson::route('/create'),
            'edit' => EditRcmcRawContactPerson::route('/{record}/edit'),
        ];
    }
}
