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
                            ->disabled()
                            ->dehydrated(true)
                            // FIX: Load existing value if present
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee_code ?? Auth::user()?->employee?->employee_code))
                            ->default(fn() => Auth::user()?->employee?->employee_code),

                        TextInput::make('employee_name')
                            ->label('Employee Name')
                            ->disabled()
                            ->dehydrated(false)
                            // FIX: Load from relationship
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->name ?? Auth::user()?->employee?->name))
                            ->default(fn() => Auth::user()?->employee?->name),

                        TextInput::make('designation')
                            ->label('Designation')
                            ->disabled()
                            ->dehydrated(false)
                            // FIX: Load from relationship
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->designation?->designation ?? Auth::user()?->employee?->designation?->designation))
                            ->default(fn() => Auth::user()?->employee?->designation?->designation),

                        TextInput::make('department')
                            ->label('Department')
                            ->disabled()
                            ->dehydrated(false)
                            // FIX: Load from relationship
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->department?->department ?? Auth::user()?->employee?->department?->department))
                            ->default(fn() => Auth::user()?->employee?->department?->department),

                        TextInput::make('office')
                            ->label('Office')
                            ->disabled()
                            ->dehydrated(false)
                            // FIX: Load from relationship
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->office?->office ?? Auth::user()?->employee?->office?->office))
                            ->default(fn() => Auth::user()?->employee?->office?->office),

                        TextInput::make('basic')
                            ->label('Basic Pay')
                            ->disabled()
                            ->dehydrated(true)
                            // FIX: Load from relationship
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->basic ?? Auth::user()?->employee?->basic))
                            ->default(fn() => Auth::user()?->employee?->basic),
                    ])
                    ->columnSpanFull(),

                // --- NEW: Display Query Reason if sent back ---
                Section::make('Attention Required')
                    ->schema([
                        Textarea::make('query_reason')
                            ->label('Reviewer Remarks / Query')
                            ->disabled()
                            ->dehydrated(false) 
                            ->rows(3)
                            // Use standard Tailwind text color for the input content
                            ->extraInputAttributes(['class' => 'text-red-600 font-medium'])
                            ->afterStateHydrated(fn ($component, $record) => $component->state($record?->remarks))
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record) => $record && $record->current_state === 'query')
                    ->icon('heroicon-m-exclamation-circle')
                    ->iconColor('danger')
                    ->extraAttributes([
                        // Using 'style' guarantees the background works without recompiling assets
                        'style' => 'background-color:#EF4444; border: 4px solid #EF4444; border-radius: 10px;', 
                        // 'class' => 'shadow-none', // Removes default card shadow for a flatter "alert" look
                    ])
                    ->collapsible(false)
                    ->columnSpanFull(),

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
                            // FIX: Load from relationship if record exists
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->office?->city ?? Auth::user()?->employee?->office?->city))
                            ->default(fn() => Auth::user()?->employee?->office?->city),

                        // --- DEPARTURE HYDRATION ---
                        DatePicker::make('tour_start_date')
                            ->label('Departure Date')
                            ->required()
                            ->live()
                            // FIX: Extract Date from dep_datetime
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->dep_datetime) {
                                    $component->state($record->dep_datetime->toDateString());
                                }
                            })
                            ->default(now())
                            // ... (Keep your existing afterStateUpdated logic here) ...
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $date = $state;
                                $time = $get('tour_start_time');
                                if ($date && $time) {
                                    $dt = Carbon::createFromFormat('Y-m-d H:i', "$date $time", config('app.timezone'));
                                    $set('dep_datetime', $dt->toIso8601String());
                                } else {
                                    $set('dep_datetime', null);
                                }
                            }),

                        TimePicker::make('tour_start_time')
                            ->label('Departure Time')
                            ->seconds(false)
                            ->required()
                            ->live()
                            // FIX: Extract Time from dep_datetime
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->dep_datetime) {
                                    $component->state($record->dep_datetime->format('H:i'));
                                }
                            })
                            ->default(now()->format('H:i'))
                            // ... (Keep your existing afterStateUpdated logic here) ...
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $time = $state;
                                $date = $get('tour_start_date');
                                if ($date && $time) {
                                    $dt = Carbon::createFromFormat('Y-m-d H:i', "$date $time", config('app.timezone'));
                                    $set('dep_datetime', $dt->toIso8601String());
                                } else {
                                    $set('dep_datetime', null);
                                }
                            }),

                        // --- ARRIVAL HYDRATION ---
                        DatePicker::make('tour_end_date')
                            ->label('Arrival Date')
                            ->required()
                            ->live()
                            // FIX: Extract Date from arr_datetime
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->arr_datetime) {
                                    $component->state($record->arr_datetime->toDateString());
                                }
                            })
                            ->default(now())
                            // ... (Keep your existing afterStateUpdated logic here) ...
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $date = $state;
                                $time = $get('tour_end_time');
                                if ($date && $time) {
                                    $dt = Carbon::createFromFormat('Y-m-d H:i', "$date $time", config('app.timezone'));
                                    $set('arr_datetime', $dt->toIso8601String());
                                } else {
                                    $set('arr_datetime', null);
                                }
                            }),

                        TimePicker::make('tour_end_time')
                            ->label('Arrival Time')
                            ->seconds(false)
                            ->required()
                            ->live()
                            // FIX: Extract Time from arr_datetime
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record?->arr_datetime) {
                                    $component->state($record->arr_datetime->format('H:i'));
                                }
                            })
                            ->default(now()->format('H:i'))
                            // ... (Keep your existing afterStateUpdated logic here) ...
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                $time = $state;
                                $date = $get('tour_end_date');
                                if ($date && $time) {
                                    $dt = Carbon::createFromFormat('Y-m-d H:i', "$date $time", config('app.timezone'));
                                    $set('arr_datetime', $dt->toIso8601String());
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
                                    ->live()
                                    // FIX: Determine currency based on which DB column has a value
                                    ->afterStateHydrated(function ($component, $record) {
                                        if (!$record) return;

                                        // If forex has value > 0, set currency to FOREIGN, else INR
                                        if ((float) $record->advance_forex > 0) {
                                            $component->state('FOREIGN');
                                        } else {
                                            $component->state('INR');
                                        }
                                    }),

                                TextInput::make('advance_amount')
                                    ->label('Advance Amount')
                                    ->numeric()
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->suffix(fn(Get $get) => $get('advance_currency') === 'INR' ? 'INR' : 'Foreign')
                                    ->default('25000.00') // Changed default to 0 to avoid confusion on new claims
                                    ->dehydrated(true)
                                    // FIX: Populate amount from the correct DB column
                                    ->afterStateHydrated(function ($component, $record) {
                                        if (!$record) return;

                                        // Load the value from either forex or inr column
                                        $amount = (float) $record->advance_forex > 0
                                            ? $record->advance_forex
                                            : $record->advance_inr;

                                        $component->state($amount);
                                    }),
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
                            ->live()
                            ->reactive()
                            ->columns(6)
                            // --- FIX PART 1: Pack fields into payload_json before saving ---
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                $data['payload_json'] = [
                                    'from_city'  => $data['from_city'] ?? null,
                                    'to_city'    => $data['to_city'] ?? null,
                                    'mode'       => $data['mode'] ?? null,
                                    'hotel_name' => $data['hotel_name'] ?? null,
                                    'nights'     => $data['nights'] ?? null,
                                    'per_night'  => $data['per_night'] ?? null,
                                ];
                                // Remove these keys so Eloquent doesn't try to save them as columns
                                unset($data['from_city'], $data['to_city'], $data['mode'], $data['hotel_name'], $data['nights'], $data['per_night']);
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                                // Logic is identical for updates
                                $data['payload_json'] = [
                                    'from_city'  => $data['from_city'] ?? null,
                                    'to_city'    => $data['to_city'] ?? null,
                                    'mode'       => $data['mode'] ?? null,
                                    'hotel_name' => $data['hotel_name'] ?? null,
                                    'nights'     => $data['nights'] ?? null,
                                    'per_night'  => $data['per_night'] ?? null,
                                ];
                                unset($data['from_city'], $data['to_city'], $data['mode'], $data['hotel_name'], $data['nights'], $data['per_night']);
                                return $data;
                            })
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
                                        'local_conveyance'  => 'Local Conveyance',
                                        'misc'              => 'Miscellaneous',
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

                                // --- FIX PART 2: Enable dehydration & Load from payload_json ---

                                Select::make('mode')
                                    ->label('Mode')
                                    ->options([
                                        'air'   => 'Air',
                                        'train' => 'Train',
                                        'taxi'  => 'Taxi',
                                        'bus'   => 'Bus',
                                        'other' => 'Other',
                                    ])
                                    ->visible(fn(Get $get) => $get('line_type') === 'travel')
                                    ->columnSpan(2)
                                    ->dehydrated(true)
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->payload_json['mode'] ?? null)),

                                TextInput::make('from_city')
                                    ->label('From')
                                    ->visible(fn(Get $get) => $get('line_type') === 'travel')
                                    ->columnSpan(2)
                                    ->dehydrated(true) // MUST be true to pass to mutation closure
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->payload_json['from_city'] ?? null)),

                                TextInput::make('to_city')
                                    ->label('To')
                                    ->visible(fn(Get $get) => $get('line_type') === 'travel')
                                    ->columnSpan(2)
                                    ->dehydrated(true)
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->payload_json['to_city'] ?? null)),

                                TextInput::make('hotel_name')
                                    ->label('Hotel')
                                    ->visible(fn(Get $get) => $get('line_type') === 'stay')
                                    ->columnSpan(3)
                                    ->dehydrated(true)
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->payload_json['hotel_name'] ?? null)),

                                TextInput::make('nights')
                                    ->label('Nights')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('line_type') === 'stay')
                                    ->columnSpan(1)
                                    ->dehydrated(true)
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->payload_json['nights'] ?? null)),

                                TextInput::make('per_night')
                                    ->label('Per Night')
                                    ->numeric()
                                    ->minValue(0)
                                    ->visible(fn(Get $get) => $get('line_type') === 'stay')
                                    ->columnSpan(2)
                                    ->dehydrated(true)
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->payload_json['per_night'] ?? null)),

                                FileUpload::make('uploads')
                                    ->label('Attachments')
                                    ->multiple()
                                    ->openable()
                                    ->downloadable()
                                    ->columnSpan(6)
                                    ->disk('public')
                                    ->directory('tour-claims')
                                    ->acceptedFileTypes([
                                        'application/pdf',
                                        'image/jpeg',
                                        'image/png',
                                        'image/webp',
                                        'application/msword',
                                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                                        'application/vnd.ms-excel',
                                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                    ])
                                    ->maxSize(5120) // 5 MB
                                    // ->rules(['clamav'])
                                    ->helperText('Allowed: PDF, JPG, Word, Excel. Max size: 5 MB. Files are scanned for viruses.'),
                            ])
                    ])
                    ->columnSpanFull(),

                // --- Totals / Settlement (UI) ---
                Section::make('Expenses Total (Auto)')
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
                            ->live()
                            // FIX: Read from payload_json
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record && isset($record->payload_json['meals_provided'])) {
                                    $component->state($record->payload_json['meals_provided']);
                                }
                            }),

                        Repeater::make('meals_provided_details')
                            ->label('If yes, give details')
                            ->visible(fn(Get $get) => $get('meals_provided') === 'yes')
                            ->columns(3)
                            // FIX: Read from payload_json
                            ->afterStateHydrated(function ($component, $record) {
                                if ($record && isset($record->payload_json['meals_details'])) {
                                    $component->state($record->payload_json['meals_details']);
                                }
                            })
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
