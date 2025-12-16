<?php

namespace App\Filament\Resources\RcmcRawReceipts;

use App\Filament\Resources\RcmcRawReceipts\Pages\CreateRcmcRawReceipt;
use App\Filament\Resources\RcmcRawReceipts\Pages\EditRcmcRawReceipt;
use App\Filament\Resources\RcmcRawReceipts\Pages\ListRcmcRawReceipts;
use App\Filament\Resources\RcmcRawReceipts\Schemas\RcmcRawReceiptForm;
use App\Filament\Resources\RcmcRawReceipts\Tables\RcmcRawReceiptsTable;
use App\Models\RcmcRawReceipt;
use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RcmcRawReceiptResource extends Resource
{
    protected static ?string $model = RcmcRawReceipt::class;

    protected static string|UnitEnum|null $navigationGroup = 'Upload RCMC Data';

    protected static ?string $navigationLabel = 'Receipts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RcmcRawReceiptForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RcmcRawReceiptsTable::configure($table);
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
            'index' => ListRcmcRawReceipts::route('/'),
            'create' => CreateRcmcRawReceipt::route('/create'),
            'edit' => EditRcmcRawReceipt::route('/{record}/edit'),
        ];
    }
}
