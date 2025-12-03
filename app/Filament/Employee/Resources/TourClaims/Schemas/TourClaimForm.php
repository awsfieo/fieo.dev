<?php

namespace App\Filament\Employee\Resources\TourClaims\Schemas;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;

class TourClaimForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // 1) Hidden employee_id – first in the schema
                Hidden::make('employee_id')
                    ->default(fn() => Filament::auth()->user()?->employee?->id)
                    ->dehydrated(true)
                    ->required(),

                // --- Employee snapshot (UI-only) ---
                Section::make('Employee Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employee_code')
                            ->label('Employee Code')
                            ->default(fn() => Auth::user()?->employee?->employee_code ?? '')
                            ->disabled()
                            ->dehydrated(true),

                        TextInput::make('employee_name')
                            ->label('Employee Name')
                            ->default(fn() => Auth::user()?->employee?->name ?? Auth::user()?->name ?? '')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('designation')
                            ->label('Designation')
                            ->default(fn() => Auth::user()?->employee?->designation?->designation ?? '')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('department')
                            ->label('Department')
                            ->default(fn() => Auth::user()?->employee?->department?->department ?? '')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('office')
                            ->label('Office')
                            ->default(fn() => Auth::user()?->employee?->office?->office ?? '')
                            ->disabled()
                            ->dehydrated(false),
                        TextInput::make('basic')
                            ->label('Basic Pay')
                            ->default(fn() => Auth::user()?->employee?->basic ?? '')
                            ->disabled()
                            ->dehydrated(true),
                    ])
                    ->columnSpanFull(),

                // --- Travel header ---
                Section::make('Travel Details')
                    ->columns(2)
                    ->schema([
                        TextInput::make('application_no')
                            ->label('Application No')
                            ->disabled()
                            ->hidden(fn($livewire) => $livewire instanceof CreateRecord)
                            ->maxLength(50),

                        Select::make('tour_type')
                            ->label('Tour Type')
                            ->options([
                                'domestic' => 'Domestic',
                                'international' => 'International',
                            ])
                            ->default('domestic')
                            ->required(),

                        TextInput::make('posting_city')
                            ->label('Posting City')
                            ->helperText('Prefilled - Change, if needed')
                            ->default(fn() => Auth::user()?->employee?->office?->city ?? null),

                        DatePicker::make('tour_start_date')
                            ->label('Departure Date')
                            ->required()
                            ->live()
                            ->default(now())
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $date = $state;
                                $time = $get('tour_start_time');

                                if ($date && $time) {
                                    $set('dep_datetime', Carbon::parse("$date $time"));
                                } else {
                                    $set('dep_datetime', null);
                                }
                            }),

                        TimePicker::make('tour_start_time')
                            ->label('Departure Time')
                            ->seconds(false)
                            ->required()
                            ->live()
                            ->default(now()->format('H:i'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $time = $state;
                                $date = $get('tour_start_date');

                                if ($date && $time) {
                                    $set('dep_datetime', Carbon::parse("$date $time"));
                                } else {
                                    $set('dep_datetime', null);
                                }
                            }),

                        DatePicker::make('tour_end_date')
                            ->label('Arrival Date')
                            ->required()
                            ->live()
                            ->default(now())
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $date = $state;
                                $time = $get('tour_end_time');

                                if ($date && $time) {
                                    $set('arr_datetime', Carbon::parse("$date $time"));
                                } else {
                                    $set('arr_datetime', null);
                                }
                            }),

                        TimePicker::make('tour_end_time')
                            ->label('Arrival Time')
                            ->seconds(false)
                            ->required()
                            ->live()
                            ->default(now()->format('H:i'))
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $time = $state;
                                $date = $get('tour_end_date');

                                if ($date && $time) {
                                    $set('arr_datetime', Carbon::parse("$date $time"));
                                } else {
                                    $set('arr_datetime', null);
                                }
                            }),


                        TextInput::make('place_of_tour')
                            ->label('Place of Tour')
                            ->required()
                            ->default('Delhi'),
                    ])
                    ->columnSpan(1),

                // --- Event / Advance ---
                Group::make()
                    ->schema([
                        Section::make('Event / Purpose')
                            ->schema([
                                Toggle::make('is_event_based')
                                    ->label('Is the tour for a FIEO event?')
                                    ->live(),

                                // When event-based, choose an event (stores event_id)
                                Section::make('Event Details')
                                    ->visible(fn(Get $get) => $get('is_event_based'))
                                    ->columnSpanFull()
                                    ->schema([
                                        Select::make('event_id')
                                            ->label('Event')
                                            ->required(fn(Get $get) => $get('is_event_based'))
                                            ->searchable()
                                            ->preload()
                                            ->options(
                                                fn() => \App\Models\Event::query()
                                                    ->where('status', 'live') // ⚠️ Ensure 'live' matches the exact string in your DB
                                                    ->pluck('slug', 'id')     // Label = slug, Value = id
                                                    ->toArray()
                                            ),
                                        // If you have App\Models\Event with 'name'
                                        // ->options(\App\Models\Event::query()
                                        //     ->orderByDesc('id')
                                        //     ->pluck('name', 'id')),
                                        // Optional UI-only fields for display/reference (do not store):
                                        // DatePicker::make('event_start_date')
                                        //     ->label('Event Start')
                                        //     ->nullable()
                                        //     ->dehydrated(false),
                                        // DatePicker::make('event_end_date')
                                        //     ->label('Event End')
                                        //     ->nullable()
                                        //     ->dehydrated(false),
                                    ]),

                                // Always capture purpose text (DB column exists)
                                Textarea::make('purpose_of_tour')
                                    ->label('Purpose of Tour')
                                    ->rows(3)
                                    ->required()
                                    ->default('Trip to Delhi'),
                            ]),

                        Section::make('Advance (If Any)')
                            ->columns(2)
                            ->schema([
                                ToggleButtons::make('advance_currency')
                                    ->label('Advance Currency')
                                    ->options([
                                        'INR'     => 'INR',
                                        'FOREIGN' => 'Foreign',
                                    ])
                                    ->inline()
                                    ->default('INR')
                                    ->live(),

                                TextInput::make('advance_amount')
                                    ->label('Advance Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->suffix(fn(Get $get) => $get('advance_currency') === 'INR' ? 'INR' : 'Foreign')
                                    ->default('25000.00')
                                    ->dehydrated(true),
                            ]),
                    ])
                    ->columnSpan(1),

                // --- Items repeater (persists to tour_claim_items) ---
                Section::make('Expense Items')
                    ->schema([
                        Repeater::make('items')
                            ->label('Items')
                            ->relationship('items')
                            ->default([])
                            ->live()       // ADD THIS
                            ->reactive()   // ADD THIS
                            ->columns(6)
                            ->schema([
                                DatePicker::make('period_from')
                                    ->label('Date')
                                    ->required()
                                    ->columnSpan(2),

                                Select::make('line_type')
                                    ->label('Head')
                                    ->options([
                                        'travel'            => 'Travel',
                                        'stay'              => 'Stay',
                                        'da'                => 'DA',
                                        'local_conveyance'  => 'Local',
                                        'misc'              => 'Misc',
                                        // add more if you enable them in UI later:
                                        // 'registration_fee' => 'Registration Fee',
                                        // 'visa_fee'         => 'Visa Fee',
                                        // 'insurance'        => 'Insurance',
                                    ])
                                    ->required()
                                    ->columnSpan(2)
                                    ->live(),

                                ToggleButtons::make('currency')
                                    ->label('Currency')
                                    ->options([
                                        'INR'     => 'INR',
                                        'FOREIGN' => 'Foreign',
                                    ])
                                    ->default('INR')
                                    ->inline()
                                    ->live()
                                    ->required()
                                    ->columnSpan(1),

                                TextInput::make('amount_inr')
                                    ->label('Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->visible(fn(Get $get) => $get('currency') === 'INR')
                                    ->required(fn(Get $get) => $get('currency') === 'INR')
                                    ->columnSpan(1)
                                    ->live(),

                                TextInput::make('amount_forex')
                                    ->label('Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->visible(fn(Get $get) => $get('currency') === 'FOREIGN')
                                    ->required(fn(Get $get) => $get('currency') === 'FOREIGN')
                                    ->columnSpan(1)
                                    ->live(),

                                TextInput::make('exchange_rate')
                                    ->label('Fx Rate')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('currency') === 'FOREIGN')
                                    ->helperText('If foreign, optionally store rate used')
                                    ->columnSpan(2),

                                TextInput::make('description')
                                    ->label('Description')
                                    ->required()
                                    ->columnSpan(4),

                                // UI-only extras packed into payload_json
                                TextInput::make('from_city')
                                    ->label('From')
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('line_type') === 'travel')
                                    ->columnSpan(2),

                                TextInput::make('to_city')
                                    ->label('To')
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('line_type') === 'travel')
                                    ->columnSpan(2),

                                Select::make('mode')
                                    ->label('Mode')
                                    ->options([
                                        'air'   => 'Air',
                                        'train' => 'Train',
                                        'taxi'  => 'Taxi',
                                        'bus'   => 'Bus',
                                        'other' => 'Other',
                                    ])
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('line_type') === 'travel')
                                    ->columnSpan(2),

                                TextInput::make('hotel_name')
                                    ->label('Hotel')
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('line_type') === 'stay')
                                    ->columnSpan(3),

                                TextInput::make('nights')
                                    ->label('Nights')
                                    ->numeric()
                                    ->minValue(0)
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('line_type') === 'stay')
                                    ->columnSpan(1),

                                TextInput::make('per_night')
                                    ->label('Per Night')
                                    ->numeric()
                                    ->minValue(0)
                                    ->dehydrated(false)
                                    ->visible(fn(Get $get) => $get('line_type') === 'stay')
                                    ->columnSpan(2),

                                FileUpload::make('uploads')
                                    ->label('Attachments')
                                    ->multiple()
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpan(6)
                                    ->disk('public')
                                    ->directory('tour-claims'),
                            ])
                    ])
                    ->columnSpanFull(),

                // --- Totals / Settlement (UI) ---
                Section::make('Totals (Auto)')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('total_inr')
                            ->label('Total (INR)')
                            ->content(function (Get $get) {
                                $rows = $get('items') ?? [];
                                $tot = 0;
                                foreach ($rows as $r) {
                                    if (($r['currency'] ?? 'INR') === 'INR') {
                                        $tot += (float)($r['amount_inr'] ?? 0);
                                    }
                                }
                                return number_format($tot, 2) . ' INR';
                            }),

                        Placeholder::make('total_foreign')
                            ->label('Total (Foreign)')
                            ->content(function (Get $get) {
                                $rows = $get('items') ?? [];
                                $tot = 0;
                                foreach ($rows as $r) {
                                    if (($r['currency'] ?? 'INR') === 'FOREIGN') {
                                        $tot += (float)($r['amount_forex'] ?? 0);
                                    }
                                }
                                return number_format($tot, 2) . ' Foreign';
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make('Settlement (Auto)')
                    ->columns(2)
                    ->schema([
                        Placeholder::make('net_inr')
                            ->label('Net INR')
                            ->content(function (Get $get) {
                                $rows   = $get('items') ?? [];
                                $totInr = 0;
                                foreach ($rows as $r) {
                                    if (($r['currency'] ?? 'INR') === 'INR') {
                                        $totInr += (float)($r['amount_inr'] ?? 0);
                                    }
                                }
                                $advInr = ($get('advance_currency') === 'INR') ? (float)($get('advance_amount') ?? 0) : 0;
                                $netInr = $totInr - $advInr;

                                $outcome = $netInr > 0 ? 'Reimbursement to employee'
                                    : ($netInr < 0 ? 'Refund to office' : 'No dues');
                                return number_format($netInr, 2) . ' INR — ' . $outcome;
                            }),

                        Placeholder::make('net_foreign')
                            ->label('Net Foreign')
                            ->content(function (Get $get) {
                                $rows   = $get('items') ?? [];
                                $totFx  = 0;
                                foreach ($rows as $r) {
                                    if (($r['currency'] ?? 'INR') === 'FOREIGN') {
                                        $totFx += (float)($r['amount_forex'] ?? 0);
                                    }
                                }
                                $advFx = ($get('advance_currency') === 'FOREIGN') ? (float)($get('advance_amount') ?? 0) : 0;
                                $netFx = $totFx - $advFx;

                                $outcome = $netFx > 0 ? 'Reimbursement to employee'
                                    : ($netFx < 0 ? 'Refund to office' : 'No dues');
                                return number_format($netFx, 2) . ' — ' . $outcome;
                            }),
                    ])
                    ->columnSpanFull(),

                Section::make('Meals Provided by Organisers')
                    ->columns(2)
                    ->schema([
                        Radio::make('meals_provided')
                            ->label('Were meals provided?')
                            ->options([
                                'no'  => 'Not Provided',
                                'yes' => 'Provided',
                            ])
                            ->inline()
                            ->live(),

                        Repeater::make('meals_provided_details')
                            ->label('If yes, give details')
                            ->visible(fn(Get $get) => $get('meals_provided') === 'yes')
                            ->columns(3)
                            ->schema([
                                DatePicker::make('date')
                                    ->label('Date')
                                    ->required()
                                    ->columnSpan(1),

                                Checkbox::make('lunch')
                                    ->label('Lunch')
                                    ->columnSpan(1),

                                Checkbox::make('dinner')
                                    ->label('Dinner')
                                    ->columnSpan(1),

                            ]),
                    ])
                    ->columnSpanFull(),

                Hidden::make('designation_id')
                    ->default(fn() => Auth::user()?->employee?->designation_id)
                    ->dehydrated(true),

                Hidden::make('department_id')
                    ->default(fn() => Auth::user()?->employee?->department_id)
                    ->dehydrated(true),

                Hidden::make('office_id')
                    ->default(fn() => Auth::user()?->employee?->office_id)
                    ->dehydrated(true),

                // --- Hidden glue to persist computed fields ---
                Hidden::make('dep_datetime')
                    ->dehydrated(true)
                    ->reactive(),

                Hidden::make('arr_datetime')
                    ->dehydrated(true)
                    ->reactive(),

                Hidden::make('advance_inr')
                    ->dehydrated(true)
                    ->live()
                    ->afterStateHydrated(function ($set, $get) {
                        $amt = (float)($get('advance_amount') ?? 0);
                        $set('advance_inr', (($get('advance_currency') ?? null) === 'INR') ? $amt : 0);
                    })
                    ->reactive(),

                Hidden::make('advance_forex')
                    ->dehydrated(true)
                    ->live()
                    ->afterStateHydrated(function ($set, $get) {
                        $amt = (float)($get('advance_amount') ?? 0);
                        $set('advance_forex', (($get('advance_currency') ?? null) === 'FOREIGN') ? $amt : 0);
                    })
                    ->reactive(),

                Hidden::make('amount_reimburse_inr')
                    ->default(0)
                    ->dehydrated(true),

                Hidden::make('amount_reimburse_forex')
                    ->default(0)
                    ->dehydrated(true),

                Hidden::make('amount_refund_inr')
                    ->default(0)
                    ->dehydrated(true),

                Hidden::make('amount_refund_forex')
                    ->default(0)
                    ->dehydrated(true),

            ]);
    }
}
