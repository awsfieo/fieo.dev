<?php

namespace App\Filament\Resources\RcmcRawHsCodes;

use App\Filament\Resources\RcmcRawHsCodes\Pages\CreateRcmcRawHsCode;
use App\Filament\Resources\RcmcRawHsCodes\Pages\EditRcmcRawHsCode;
use App\Filament\Resources\RcmcRawHsCodes\Pages\ListRcmcRawHsCodes;
use App\Filament\Resources\RcmcRawHsCodes\Schemas\RcmcRawHsCodeForm;
use App\Filament\Resources\RcmcRawHsCodes\Tables\RcmcRawHsCodesTable;
use App\Models\RcmcRawHsCode;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RcmcRawHsCodeResource extends Resource
{
    protected static ?string $model = RcmcRawHsCode::class;

    protected static string|UnitEnum|null $navigationGroup = 'Upload RCMC Data';

    protected static ?string $navigationLabel = 'HS Codes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    public static function form(Schema $schema): Schema
    {
        return RcmcRawHsCodeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RcmcRawHsCodesTable::configure($table);
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
            'index' => ListRcmcRawHsCodes::route('/'),
            'create' => CreateRcmcRawHsCode::route('/create'),
            'edit' => EditRcmcRawHsCode::route('/{record}/edit'),
        ];
    }
}
