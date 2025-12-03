<?php

namespace App\Filament\Resources\Departments\Schemas;

use App\Models\Department;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DepartmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('sort_id')
                ->numeric()
                ->minValue(0)
                ->default(0),

            TextInput::make('department')
                ->required(),

            TextInput::make('long_title'),

            TextInput::make('short_title'),

            Select::make('type')
                ->options(Department::TYPE_LABELS)   // HO, DO, RO, CO → labels
                ->native(false)
                ->searchable()
                ->nullable(),

            Select::make('region')
                ->options(Department::REGION_LABELS)   // HO, DO, RO, CO → labels
                ->native(false)
                ->searchable()
                ->nullable(),

            // Parent department (self-referencing)
            Select::make('parent_id')
                ->label('Parent Department')
                ->relationship('parent', 'department')   // keeps it a proper belongsTo field
                ->searchable()
                ->preload()
                ->native(false)
                ->nullable()
                // Exclude the current record from choices & power the search dropdown:
                ->getSearchResultsUsing(function (string $search, ?Department $record) {
                    $q = Department::query()
                        ->when($record?->exists, fn($q2) => $q2->whereKeyNot($record->getKey()))
                        // Postgres-friendly case-insensitive search:
                        ->where('department', 'ilike', "%{$search}%")
                        ->orderBy('department')
                        ->limit(50);

                    return $q->pluck('department', 'id')->toArray(); // [id => label]
                })
                // How to display the selected label:
                ->getOptionLabelUsing(fn($value) => Department::find($value)?->department),

            // Office (belongsTo Office)
            Select::make('office_id')
                ->label('Office')
                ->relationship('office', 'office')
                ->searchable()
                ->preload()
                ->native(false)
                ->nullable(),

            TextInput::make('gstin')
                ->label('GSTIN')
                ->maxLength(15),

            TextInput::make('mid')
                ->label('MID')
                ->maxLength(255),

            // TextInput::make('url')
            //     ->label('URL')
            //     ->url()
            //     ->maxLength(255),

            Toggle::make('is_active')
                ->label('Active')
                ->default(true),
        ])->columns(3);
    }
}
