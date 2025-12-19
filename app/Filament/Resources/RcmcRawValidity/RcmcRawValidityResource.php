<?php

namespace App\Filament\Resources\RcmcRawValidity;

use App\Filament\Resources\RcmcRawValidity\Pages\CreateRcmcRawValidity;
use App\Filament\Resources\RcmcRawValidity\Pages\EditRcmcRawValidity;
use App\Filament\Resources\RcmcRawValidity\Pages\ListRcmcRawValidity;
use App\Filament\Resources\RcmcRawValidity\Schemas\RcmcRawValidityForm;
use App\Filament\Resources\RcmcRawValidity\Tables\RcmcRawValidityTable;
use App\Models\RcmcRawValidity;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RcmcRawValidityResource extends Resource
{
    protected static ?string $model = RcmcRawValidity::class;

    protected static string|UnitEnum|null $navigationGroup = 'Upload RCMC Data';

    protected static ?string $navigationLabel = 'Validity & Turnover';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    public static function form(Schema $schema): Schema
    {
        return RcmcRawValidityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RcmcRawValidityTable::configure($table);
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
            'index' => ListRcmcRawValidity::route('/'),
            'create' => CreateRcmcRawValidity::route('/create'),
            'edit' => EditRcmcRawValidity::route('/{record}/edit'),
        ];
    }
}
