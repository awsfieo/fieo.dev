<?php

namespace App\Filament\Resources\RcmcRawDirectors;

use App\Filament\Resources\RcmcRawDirectors\Pages\CreateRcmcRawDirector;
use App\Filament\Resources\RcmcRawDirectors\Pages\EditRcmcRawDirector;
use App\Filament\Resources\RcmcRawDirectors\Pages\ListRcmcRawDirectors;
use App\Filament\Resources\RcmcRawDirectors\Schemas\RcmcRawDirectorForm;
use App\Filament\Resources\RcmcRawDirectors\Tables\RcmcRawDirectorsTable;
use App\Models\RcmcRawDirector;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RcmcRawDirectorResource extends Resource
{
    protected static ?string $model = RcmcRawDirector::class;

   protected static string|UnitEnum|null $navigationGroup = 'Upload RCMC Data';

    protected static ?string $navigationLabel = 'Directors Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    public static function form(Schema $schema): Schema
    {
        return RcmcRawDirectorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RcmcRawDirectorsTable::configure($table);
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
            'index' => ListRcmcRawDirectors::route('/'),
            'create' => CreateRcmcRawDirector::route('/create'),
            'edit' => EditRcmcRawDirector::route('/{record}/edit'),
        ];
    }
}
