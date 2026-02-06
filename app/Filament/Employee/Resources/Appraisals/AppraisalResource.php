<?php

namespace App\Filament\Employee\Resources\Appraisals;

use App\Filament\Employee\Resources\Appraisals\Pages\CreateAppraisal;
use App\Filament\Employee\Resources\Appraisals\Pages\EditAppraisal;
use App\Filament\Employee\Resources\Appraisals\Pages\ListAppraisals;
use App\Filament\Employee\Resources\Appraisals\Pages\ViewAppraisal;
use App\Filament\Employee\Resources\Appraisals\Schemas\AppraisalForm;
use App\Filament\Employee\Resources\Appraisals\Schemas\AppraisalInfolist;
use App\Filament\Employee\Resources\Appraisals\Tables\AppraisalsTable;
use App\Models\Appraisal;
use UnitEnum;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class AppraisalResource extends Resource
{
    protected static ?string $model = Appraisal::class;

    protected static string|UnitEnum|null $navigationGroup = 'Froms and Formats';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?string $recordTitleAttribute = 'employee_name';

    public static function form(Schema $schema): Schema
    {
        return AppraisalForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return AppraisalInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppraisalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        // 1. Only Super Admin sees everything (for debugging)
        // REMOVED 'DG & CEO' from here so they fall through to the filter below
        if ($user->hasRole('Super Admin')) {
            return parent::getEloquentQuery();
        }

        // 2. Everyone else (Employees AND DG & CEO) sees:
        //    a) Their OWN appraisals (Creator)
        //    b) Appraisals currently PENDING WITH them (Reviewer)
        return parent::getEloquentQuery()
            ->where(function (Builder $query) use ($user) {
                $query->where('employee_id', $user->employee?->id)
                    ->orWhere('pending_with', $user->employee?->employee_code);
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAppraisals::route('/'),
            'create' => CreateAppraisal::route('/create'),
            'view' => ViewAppraisal::route('/{record}'),
            'edit' => EditAppraisal::route('/{record}/edit'),
        ];
    }
}
