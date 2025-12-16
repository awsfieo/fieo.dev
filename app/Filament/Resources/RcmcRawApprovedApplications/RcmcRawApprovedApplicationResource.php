<?php

namespace App\Filament\Resources\RcmcRawApprovedApplications;

use App\Filament\Resources\RcmcRawApprovedApplications\Pages\CreateRcmcRawApprovedApplication;
use App\Filament\Resources\RcmcRawApprovedApplications\Pages\EditRcmcRawApprovedApplication;
use App\Filament\Resources\RcmcRawApprovedApplications\Pages\ListRcmcRawApprovedApplications;
use App\Filament\Resources\RcmcRawApprovedApplications\Schemas\RcmcRawApprovedApplicationForm;
use App\Filament\Resources\RcmcRawApprovedApplications\Tables\RcmcRawApprovedApplicationsTable;
use App\Models\RcmcRawApprovedApplication;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RcmcRawApprovedApplicationResource extends Resource
{
    protected static ?string $model = RcmcRawApprovedApplication::class;

    protected static string|UnitEnum|null $navigationGroup = 'Upload RCMC Data';

    protected static ?string $navigationLabel = 'Approved Applications';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RcmcRawApprovedApplicationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RcmcRawApprovedApplicationsTable::configure($table);
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
            'index' => ListRcmcRawApprovedApplications::route('/'),
            'create' => CreateRcmcRawApprovedApplication::route('/create'),
            'edit' => EditRcmcRawApprovedApplication::route('/{record}/edit'),
        ];
    }
}
