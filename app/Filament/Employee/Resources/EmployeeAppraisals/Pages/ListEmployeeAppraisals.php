<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Pages;

use App\Filament\Employee\Resources\EmployeeAppraisals\EmployeeAppraisalResource;
use App\Models\EmployeeAppraisal;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section; // <--- The Alternative
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Schemas\Schema;

class ListEmployeeAppraisals extends ListRecords
{
    protected static string $resource = EmployeeAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('enable_appraisal_form')
                ->label('Enable Appraisal Form')
                ->icon('heroicon-m-calendar-days')
                ->color('success')
                ->modalHeading('Schedule Appraisal Submission Period')
                ->modalSubmitActionLabel('Confirm & Enable')

                // 1. Fill Form Defaults
                ->fillForm(function (): array {
                    $currentYear = date('Y');
                    $existingConfig = EmployeeAppraisal::query()
                        ->where('appraisal_year', $currentYear)
                        ->whereNotNull('appraisal_start_date')
                        ->first();

                    return [
                        'year' => $currentYear,
                        'month' => 'All',
                        'start_date' => $existingConfig?->appraisal_start_date ?? now(),
                        'end_date' => $existingConfig?->appraisal_end_date,
                    ];
                })
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('year')
                                ->label('Appraisal Year')
                                ->numeric()
                                ->required()
                                ->prefixIcon('heroicon-m-calendar')
                                ->live(),

                            Select::make('month')
                                ->label('Appraisal Month')
                                ->options([
                                    'All'     => 'All (April & October)',
                                    'April'   => 'April',
                                    'October' => 'October',
                                ])
                                ->default('All')
                                ->required()
                                ->prefixIcon('heroicon-m-arrow-path')
                                ->live(),

                            DatePicker::make('start_date')
                                ->label('Appraisal Start Date')
                                ->required()
                                ->native(false)
                                ->prefixIcon('heroicon-m-calendar-days')
                                ->live(),

                            DatePicker::make('end_date')
                                ->label('Appraisal End Date')
                                ->required()
                                ->native(false)
                                ->afterOrEqual('start_date')
                                ->prefixIcon('heroicon-m-calendar-days')
                                ->live(),
                        ]),


                    // 1. Grid to hold the two separate blocks
                    Grid::make(1)
                        ->schema([

                            // BLOCK 2: THE HEADER (Icon + Title Only)
                            Section::make('Action Summary')
                                ->icon('heroicon-m-exclamation-triangle') // Standard Filament Icon
                                ->iconColor('warning')
                                ->compact()
                                ->schema([
                                    Section::make('summary_details')
                                        ->heading(false) // Disable the header completely for this block
                                        ->extraAttributes(['class' => '!mt-0 !pt-0 border-t-0']) // CSS to merge it visually with the top block
                                        ->description(function ($get) {
                                            $year = $get('year') ?? '...';
                                            $month = $get('month') === 'All' ? 'April & October' : $get('month');
                                            $start = $get('start_date') ? Carbon::parse($get('start_date'))->format('d M Y') : '...';
                                            $end = $get('end_date') ? Carbon::parse($get('end_date'))->format('d M Y') : '...';

                                            return new HtmlString("
                                                <div class='text-sm text-gray-600'>
                                                    <p>Are you sure you want to enable employees to duly fill their Appraisal Form for appraisal month(s) <strong>{$month} {$year}</strong>?</p>
                                                    <ul style='list-style-type: disc; margin-left: 1.5rem; margin-top: 0.5rem; color: #d97706;'>
                                                        <li><strong>Enable Appraisal Submission Period:</strong> {$start} to {$end}</li>
                                                        <li><strong>Impact:</strong> All 'Pending' records will be updated.</li>
                                                        <li><strong>Note:</strong> These dates can be edited anytime, if required.</li>
                                                        <li><strong>Note:</strong> Individual employees can be granted extension, if required.</li>
                                                    </ul>
                                                </div>
                                                ");
                                        }),
                                ]),

                        ]),
                ])

                ->action(function (array $data) {
                    $query = EmployeeAppraisal::query()
                        ->where('appraisal_year', $data['year'])
                        ->where('status', 'Pending');

                    if ($data['month'] !== 'All') {
                        $query->where('appraisal_month', $data['month']);
                    }

                    $count = $query->update([
                        'appraisal_start_date' => $data['start_date'],
                        'appraisal_end_date'   => $data['end_date'],
                        'updated_at'           => now(),
                    ]);

                    if ($count > 0) {
                        $cycleLabel = $data['month'] === 'All' ? 'April & October' : $data['month'];

                        Notification::make()
                            ->title('Appraisal Forms Enabled')
                            ->body("Successfully scheduled dates for {$count} pending employees for {$cycleLabel} {$data['year']}.")
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('No Records Updated')
                            ->body("No 'Pending' appraisals found for the selected criteria.")
                            ->warning()
                            ->send();
                    }
                }),

            // --- NEW EXCLUSIVE ACTION: Release Final Appraisal ---
            Action::make('release_final_appraisal')
                ->label('Release Final Appraisal')
                ->icon('heroicon-m-check-badge')
                ->color('danger')
                // Visible ONLY to DG & CEO
                ->visible(fn() => Auth::user()->hasRole('DG & CEO'))
                ->modalHeading('Release Final Appraisal')
                ->modalSubmitActionLabel('Confirm Action')
                ->fillForm(fn() => ['year' => date('Y'), 'month' => 'All'])
                ->schema([
                    // 1. WARNING SECTION
                    Section::make('Warning')
                        ->icon('heroicon-m-exclamation-triangle')
                        ->iconColor('warning')
                        ->compact()
                        ->schema([]) // Empty schema, using description for text
                        ->description(new HtmlString("
                            <div class='text-sm text-gray-600'>
                                <p><strong>Warning:</strong> This action will <strong>LOCK</strong> the appraisal process and <strong>RELEASE</strong> the results.</p>
                                <p class='mt-1'>Once released, <strong>HoD Personnel</strong> will be able to view the Increment Percentage granted to the Employees.</p>
                            </div>
                        ")),

                    // 2. SELECTION FIELDS
                    Grid::make(2)
                        ->schema([
                            TextInput::make('year')
                                ->label('Appraisal Year')
                                ->numeric()
                                ->required()
                                ->default(date('Y'))
                                ->prefixIcon('heroicon-m-calendar'),

                            Select::make('month')
                                ->label('Appraisal Month')
                                ->options([
                                    'All'     => 'All (April & October)',
                                    'April'   => 'April',
                                    'October' => 'October',
                                ])
                                ->default('All')
                                ->required()
                                ->prefixIcon('heroicon-m-arrow-path'),
                        ]),

                    // 3. TOGGLES
                    Section::make('Actions')
                        ->schema([
                            Toggle::make('lock_appraisal')
                                ->label('Lock the Appraisal')
                                ->onColor('warning')
                                ->helperText('Prevents further modifications to the appraisal cycle.'),

                            Toggle::make('release_appraisal')
                                ->label('Release the Appraisal')
                                ->onColor('success')
                                ->helperText('Changes status from "Processed" to "Released".'),
                        ])
                ])
                ->action(function (array $data) {
                    // 1. Release Logic
                    if ($data['release_appraisal']) {
                        $query = \App\Models\EmployeeAppraisal::query()
                            ->where('appraisal_year', $data['year'])
                            ->where('status', 'Processed'); // Only release Processed ones

                        if ($data['month'] !== 'All') {
                            $query->where('appraisal_month', $data['month']);
                        }

                        $count = $query->update([
                            'status' => 'Released',
                            'updated_at' => now()
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->title('Appraisals Released')
                            ->body("Successfully released {$count} appraisals.")
                            ->success()
                            ->send();
                    }

                    // 2. Lock Logic (To be implemented later)
                    if ($data['lock_appraisal']) {
                        // Placeholder for lock logic
                    }
                }),

            // CreateAction::make(),
        ];
    }
}
