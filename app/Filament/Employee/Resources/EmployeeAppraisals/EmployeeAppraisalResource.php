<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals;

use App\Filament\Employee\Resources\EmployeeAppraisals\Pages\CreateEmployeeAppraisal;
use App\Filament\Employee\Resources\EmployeeAppraisals\Pages\EditEmployeeAppraisal;
use App\Filament\Employee\Resources\EmployeeAppraisals\Pages\ListEmployeeAppraisals;
use App\Filament\Employee\Resources\EmployeeAppraisals\Schemas\EmployeeAppraisalForm;
use App\Filament\Employee\Resources\EmployeeAppraisals\Tables\EmployeeAppraisalsTable;
use BackedEnum;
use App\Models\EmployeeAppraisal;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Illuminate\Contracts\Support\Htmlable;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EmployeeAppraisalResource extends Resource
{
    protected static ?string $model = EmployeeAppraisal::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|\UnitEnum|null $navigationGroup = 'Personnel Dept';

    protected static ?string $navigationLabel = 'Employee Appraisals';

    protected static ?string $recordTitleAttribute = 'name';

    public static function canViewAny(): bool
    {
        return Auth::user()->hasAnyRole(['Super Admin', 'HOD Personnel', 'DG & CEO']);
    }

    public static function canCreate(): bool
    {
        // return Auth::user()->hasAnyRole(['Super Admin', 'HOD Personnel', 'DG & CEO']);

        return false; // Disable creation of new appraisal configurations through the UI
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return EmployeeAppraisalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeAppraisalsTable::configure($table);
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
            'index' => ListEmployeeAppraisals::route('/'),
            'create' => CreateEmployeeAppraisal::route('/create'),
            'edit' => EditEmployeeAppraisal::route('/{record}/edit'),
        ];
    }
}
