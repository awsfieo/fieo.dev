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

        // 1. Super Admin: See everything (Standard query, no grouping needed usually)
        if ($user->hasRole('Super Admin')) {
            return parent::getEloquentQuery();
        }

        // 2. Everyone Else: Apply Grouping Logic & Filters
        $employeeId = $user->employee?->id ?? 0;

        return parent::getEloquentQuery()
            // A. Select all standard columns so data isn't lost
            ->addSelect(['*'])

            // B. Create the Virtual Column 'record_type' for Grouping
            ->selectRaw("
                CASE 
                    WHEN employee_id = ? THEN 'My Appraisal' 
                    ELSE 'Staff Evaluation' 
                END as record_type
            ", [$employeeId])

            // C. Filter: Only see Own Appraisals OR Pending Reviews
            ->where(function (Builder $query) use ($user) {
                $query->where('employee_id', $user->employee?->id)
                    ->orWhere('pending_with', $user->employee?->employee_code);
            })

            // D. Order by Group (My Appraisal first, then Staff)
            ->orderBy('record_type', 'asc');
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

    public static function getNavigationBadge(): ?string
    {
        // 1. Get Current User's Employee Info
        $user = Auth::user();
        if (! $user || ! $user->employee) {
            return null;
        }

        // 2. Count records waiting for THIS user, excluding their own form
        $count = static::getModel()::query()
            ->where('pending_with', $user->employee->employee_code)
            ->where('employee_id', '!=', $user->employee->id) // Safety check to exclude own
            ->count();

        // 3. Return count (or null if 0 to hide the badge)
        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        // Optional: Make it red ('danger') to grab attention
        return 'danger';
    }
}
