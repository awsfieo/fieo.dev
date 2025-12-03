<?php

namespace App\Filament\Employee\Resources\TourClaims;

use App\Filament\Employee\Resources\TourClaims\Pages\CreateTourClaim;
use App\Filament\Employee\Resources\TourClaims\Pages\EditTourClaim;
use App\Filament\Employee\Resources\TourClaims\Pages\ListTourClaims;
use App\Filament\Employee\Resources\TourClaims\Pages\ViewTourClaim;
use App\Filament\Employee\Resources\TourClaims\Schemas\TourClaimForm;
use App\Filament\Employee\Resources\TourClaims\Schemas\TourClaimInfolist;
use App\Filament\Employee\Resources\TourClaims\Tables\TourClaimsTable;
use App\Models\TourClaim;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Log;


class TourClaimResource extends Resource
{
    protected static ?string $model = TourClaim::class;

    protected static string|UnitEnum|null $navigationGroup = 'Froms and Formats';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    protected static ?string $recordTitleAttribute = 'application_no';

    public static function form(Schema $schema): Schema
    {
        return TourClaimForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return TourClaimInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TourClaimsTable::configure($table);
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
            'index' => ListTourClaims::route('/'),
            'create' => CreateTourClaim::route('/create'),
            'view' => ViewTourClaim::route('/{record}'),
            'edit' => EditTourClaim::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {

        // show only own claims in employee panel
        return parent::getEloquentQuery()->where('employee_id', filament()->auth()->user()?->employee?->id ?? 0);
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        return static::prepareFormData($data, isCreate: true);
    }

    public static function mutateFormDataBeforeSave(array $data): array
    {
        return static::prepareFormData($data, isCreate: false);
    }

    protected static function prepareFormData(array $data, bool $isCreate): array
    {
        $data['employee_id'] = filament()->auth()->user()?->employee?->id;
        $data['office_id']   = filament()->auth()->user()?->employee?->office_id;

        $data['purpose_of_tour'] = $data['purpose_of_tour'] ?? ($data['event_name'] ?? '');

        if (!empty($data['tour_start_date']) && !empty($data['tour_start_time'])) {
            $data['dep_datetime'] = \Carbon\Carbon::parse($data['tour_start_date'] . ' ' . $data['tour_start_time']);
        }

        if (!empty($data['tour_end_date']) && !empty($data['tour_end_time'])) {
            $data['arr_datetime'] = \Carbon\Carbon::parse($data['tour_end_date'] . ' ' . $data['tour_end_time']);
        }

        // advances from the toggle + amount
        $advCurrency = $data['advance_currency'] ?? null;
        $advAmount   = (float)($data['advance_amount'] ?? 0);
        $data['advance_inr']   = $advCurrency === 'INR'     ? $advAmount : 0;
        $data['advance_forex'] = $advCurrency === 'FOREIGN' ? $advAmount : 0;

        // meta
        $data['payload_json'] = [
            'meals_provided' => $data['meals_provided'] ?? 'no',
            'meals_details'  => $data['meals_provided_details'] ?? [],
        ];

        unset($data['advance_amount']);
        // Do not try to use $data['items'] here; relationship is handled separately.
        return $data;
    }
}
