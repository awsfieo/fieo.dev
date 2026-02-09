<?php

namespace App\Filament\Employee\Resources\Appraisals\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Slider;
use App\Models\Appraisal;
use App\Models\EmployeeAppraisal;
use Filament\Forms\Components\Slider\Enums\PipsMode;
use Filament\Schemas\Components\Tabs;


class AppraisalForm
{
    /**
     * Helper to create the styled blue numbered labels.
     */
    protected static function styledLabel(string $number, string $text): HtmlString
    {
        return new HtmlString("
            <span style='color: #2563eb; font-weight: 800; font-size: 1.1em; margin-right: 0.25rem;'>{$number}.</span>
            <span style='font-weight: 600; color: #2563eb; font-size: 1.1em'>{$text}</span>
        ");
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- Section 1: Header Details ---
                Tabs::make('AppraisalDetails')
                    ->columnSpanFull()
                    ->tabs([
                        // TAB 1: Employee Details & Appraisal Info
                        Tabs\Tab::make('Appraisal Details')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                TextInput::make('application_no')
                                    ->label('Appraisal Ref No')
                                    ->placeholder('Auto-generated')
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('status')
                                    ->label('Current Status')
                                    ->default('draft')
                                    ->formatStateUsing(fn(?string $state): string => ucfirst(str_replace('_', ' ', $state ?? 'draft')))
                                    ->disabled()
                                    ->dehydrated(false),
                                TextInput::make('appraisal_year')
                                    ->label('Appraisal Year')
                                    ->default(date('Y'))
                                    ->disabled()
                                    ->dehydrated(),
                                TextInput::make('appraisal_month')
                                    ->label('Appraisal Month')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(
                                        fn() => EmployeeAppraisal::where('employee_code', Auth::user()->employee?->employee_code)
                                            ->where('appraisal_year', date('Y'))
                                            ->value('appraisal_month') // Gets 'April' or 'October'
                                    ),
                                // TextInput::make('appraisal_month')
                                //     ->label('Appraisal Month')
                                //     ->default('April')
                                //     ->disabled()
                                //     ->dehydrated(),
                            ])->columnSpanFull()
                            ->columns(4),

                        // --- Section 2: Employee Details (Read Only) ---
                        Tabs\Tab::make('Employee Details')
                            ->icon('heroicon-m-user')
                            ->schema([

                                TextInput::make('employee_code')
                                    ->label('Employee Code')
                                    ->dehydrated()
                                    ->disabled()
                                    ->default(fn() => Auth::user()->employee?->employee_code)
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee_code ?? Auth::user()->employee?->employee_code)),

                                TextInput::make('employee_name')
                                    ->label('Name')
                                    ->dehydrated(false)
                                    ->disabled()
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->name ?? Auth::user()->employee?->name)),

                                TextInput::make('designation_name')
                                    ->label('Designation')
                                    ->dehydrated(false)
                                    ->disabled()
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->designation?->designation ?? Auth::user()->employee?->designation?->designation)),

                                TextInput::make('department_name')
                                    ->label('Department')
                                    ->dehydrated(false)
                                    ->disabled()
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->department?->department ?? Auth::user()->employee?->department?->department)),

                                TextInput::make('office_name')
                                    ->label('Office')
                                    ->dehydrated(false)
                                    ->disabled()
                                    ->afterStateHydrated(fn($component, $record) => $component->state($record?->office?->office ?? Auth::user()->employee?->office?->office)),

                                Hidden::make('employee_id')
                                    ->default(fn() => Auth::user()->employee?->id),
                            ])->columnSpanFull()
                            ->columns(3),

                        // --- NEW TAB: FILE HISTORY ---
                        Tabs\Tab::make('File History')
                            ->icon('heroicon-m-clock')
                            ->schema([
                                Repeater::make('file_history')
                                    ->label('')
                                    ->schema([
                                        TextInput::make('timestamp')
                                            ->label('Date & Time')
                                            ->disabled()
                                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y') : '-')
                                            ->dehydrated(false),
                                        TextInput::make('action')
                                            ->label('Action Taken')
                                            ->disabled()
                                            ->dehydrated(false),
                                        TextInput::make('actor_name')
                                            ->label('Official')
                                            ->disabled()
                                            ->dehydrated(false),

                                        // REMOVED 'pending_with' from here

                                        TextInput::make('remarks')
                                            ->label('Remarks')
                                            ->columnSpan(2)
                                            ->disabled()
                                            ->dehydrated(false),
                                    ])
                                    ->columns(5) // Reduced columns from 6 to 5
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->dehydrated(false)
                                    ->columnSpanFull(),

                                // --- NEW: Pending With (Outside Repeater) ---
                                Section::make()
                                    ->schema([
                                        TextInput::make('current_pending_with')
                                            ->label('Currently Pending With')
                                            ->disabled()
                                            ->dehydrated(false)
                                            // Fetches the name via the 'pendingWith' relationship on the Appraisal model
                                            ->formatStateUsing(fn($record) => $record?->pendingWith?->name ?? 'N/A')
                                            ->prefixIcon('heroicon-m-user')
                                            ->extraInputAttributes(['style' => 'font-weight: bold; color: #d97706;']) // Warning color styling
                                    ])
                            ]),
                    ]),
                // --- Section 3: Part A - Employee Self Appraisal ---
                Tabs::make('AppraisalProcess')
                    ->columnSpanFull()
                    ->persistTabInQueryString()
                    ->tabs([
                        // TAB 1: APPRAISAL FORM
                        Tabs\Tab::make('Appraisal Form')
                            ->icon('heroicon-m-identification')
                            ->schema([

                                Section::make('Appraisal Form')
                                    ->description('To be filled by the Employee')
                                    ->schema([
                                        RichEditor::make('appraisal_form_data.job_profile')
                                            ->label(self::styledLabel('1', 'Define your job profile'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->required()
                                            // Logic: Disabled (Read Only) if status is NOT draft
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        ToggleButtons::make('appraisal_form_data.job_satisfaction')
                                            ->label(self::styledLabel('2', 'How satisfied are you with your job profile?'))
                                            ->options([
                                                'Not Satisfied' => 'Not Satisfied',
                                                'Somewhat Satisfied' => 'Somewhat Satisfied',
                                                'Satisfied' => 'Satisfied',
                                                'Extremely Satisfied' => 'Extremely Satisfied',
                                            ])
                                            ->colors([
                                                'Not Satisfied' => 'danger',
                                                'Somewhat Satisfied' => 'warning',
                                                'Satisfied' => 'success',
                                                'Extremely Satisfied' => 'primary',
                                            ])
                                            ->icons([
                                                'Not Satisfied' => 'heroicon-o-hand-thumb-down',
                                                'Somewhat Satisfied' => 'heroicon-o-face-frown',
                                                'Satisfied' => 'heroicon-o-face-smile',
                                                'Extremely Satisfied' => 'heroicon-o-hand-thumb-up',
                                            ])
                                            ->columns(['default' => 1, 'sm' => 2, 'lg' => 4])
                                            ->required()
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()

                                            ->columnSpanFull(),

                                        RichEditor::make('appraisal_form_data.job_enrichment')
                                            ->label(self::styledLabel('3', 'Outline what you value most about your job profile and / or changes, 
                                            if any, that could help better utilise your potential within the Federation'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('appraisal_form_data.achievements')
                                            ->label(self::styledLabel('4', 'What were your achievements during the review period and how did you achieve them?'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('appraisal_form_data.performance_gaps')
                                            ->label(self::styledLabel('5', 'What areas of your individual performance could have been better during the review period?
                                            What support is required to improve the performance?'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('appraisal_form_data.career_goals')
                                            ->label(self::styledLabel('6', 'What are your medium to long-term career goals? How can the Federation help you to achieve them?'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('appraisal_form_data.training_needs')
                                            ->label(self::styledLabel('7', 'Outline specific training and mentoring programs which would improve your performance 
                                            and make you more relevant and valuable to the Federation.'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->disabled(fn($record) => $record && $record->status !== 'draft')
                                            ->dehydrated()
                                            ->columnSpanFull(),
                                    ])
                                    ->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color: #2563eb; padding: 0.25rem; border-radius: 0.5rem;']),
                            ]),

                        // TAB 2: COMMON EVALUATION

                        // --- Section 4: Part B - Evaluation Form ---
                        Tabs\Tab::make('Evaluation Form')
                            ->id('evaluation-form')
                            ->icon('heroicon-m-clipboard-document-check')
                            ->visible(
                                fn($record) =>
                                Auth::user()->hasAnyRole(['HOD', 'Regional Head', 'Chapter Head', 'DG & CEO']) &&
                                    $record &&
                                    $record->status !== 'draft' &&
                                    Auth::user()->employee?->id !== $record->employee_id
                            )
                            ->schema([
                                Section::make('Evaluation Form')
                                    ->description('To be filled by the Supervising Officer')
                                    ->schema([
                                        ToggleButtons::make('evaluation_form_data.agree_with_employee')
                                            ->label(self::styledLabel('1', 'Do you agree with the information submitted by the employee in the Appraisal Form?'))
                                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                                            ->columns(8)
                                            ->colors(['Yes' => 'success', 'No' => 'danger'])
                                            ->icons(['Yes' => 'heroicon-o-hand-thumb-up', 'No' => 'heroicon-o-hand-thumb-down'])
                                            ->required()
                                            ->live()
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)),

                                        RichEditor::make('evaluation_form_data.disagreement') // Renamed to match Infolist
                                            ->label(self::styledLabel('', 'Outline the Disagreements'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            // Visibility: Show only if "No" is selected. 
                                            // (Remove this line if you want the Comments box to be visible always)
                                            ->visible(fn(Get $get) => $get('evaluation_form_data.agree_with_employee') === 'No')
                                            ->required(fn(Get $get) => $get('evaluation_form_data.agree_with_employee') === 'No')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('evaluation_form_data.competency_comparison')
                                            ->label(self::styledLabel('2', 'Draw a comparison of the employee\'s job competencies vis-a-vis others with the same job profile'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)),

                                        RichEditor::make('evaluation_form_data.initiative')
                                            ->label(self::styledLabel('3', 'Enumerate the employee\'s drive to take initiative and innovation'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)),

                                        RichEditor::make('evaluation_form_data.accomplishments')
                                            ->label(self::styledLabel('4', 'Outline the employee\'s Outstanding accomplishments during the review period'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)),

                                        // Ratings Group
                                        Section::make('Skills and Competency Rating (1-10)')
                                            ->label(self::styledLabel('5', 'Employee Skills and Competency Score'))
                                            ->columns(2)
                                            ->description('Objectively rate each competency on a scale of 1–10 (1 = poor, 10 = excellent). The average score must be at least 3.0')
                                            ->schema([
                                                self::rating('knowledge', 'Subject Knowledge'),
                                                self::rating('verbal_skills', 'Verbal Skills'),
                                                self::rating('written_skills', 'Written Skills'),
                                                self::rating('computer_skills', 'IT Skills'),
                                                self::rating('teamwork', 'Teamwork and Collaboration'),
                                                self::rating('discipline', 'Self-Discipline and Work Ethics'),
                                                // self::rating('relationships', 'Interpersonal Relations'),
                                                self::rating('obedience', 'Obedience and Insurbordination'),
                                                self::rating('planning', 'Planning and Time Management'),
                                                self::rating('responsibility', 'Shoulder Additional Responsibilities'),
                                                self::rating('adaptability', 'Adaptability and Flexibility'),
                                                // self::rating('leadership', 'Leadership Qualities'),

                                                // --- NEW: AVERAGE CALCULATION ---
                                                TextInput::make('average_rating_display')
                                                    ->label('Average Score')
                                                    // 1. Calculate on Page Load (Edit Mode)
                                                    ->formatStateUsing(fn(Get $get) => self::calculateAverage($get))
                                                    // 2. Make it Read-Only
                                                    ->readOnly()
                                                    ->dehydrated(false) // Do not save this specific field to DB
                                                    // 3. Styling: Remove borders/bg to make it look like text
                                                    ->extraInputAttributes([
                                                        'style' => 'font-size: 1.5rem; font-weight: 800; color: #2563eb; border: none; background: transparent; padding: 0;',
                                                    ])
                                                    ->columnSpan(1),
                                            ])
                                            ->dehydrated()
                                            // Only editable when submitted & pending with user. Otherwise, it renders as disabled inputs.
                                            ->disabled(fn($record) => !($record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)),


                                        RichEditor::make('evaluation_form_data.overall_assessment')
                                            ->label(self::styledLabel('6', 'Overall Assessment'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->required()
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color:rgb(33, 102, 17); padding: 0.25rem; border-radius: 0.5rem;']),
                            ]),
                        // --- Section 5: Part C - Regional Head Assessment ---

                        Tabs\Tab::make('Regional Head Review')
                            ->id('regional-head-review')
                            ->icon('heroicon-m-building-office')
                            ->visible(
                                fn($record) =>
                                $record &&
                                    // 1. Role Check
                                    Auth::user()->hasAnyRole(['Regional Head', 'DG & CEO']) &&
                                    // 2. Status Check
                                    in_array($record->status, ['regional_head_review_pending', 'final_assessment_pending', 'closed']) &&
                                    // 3. CRITICAL FIX: Only show if it's currently pending for RH (to fill) OR if data actually exists (to view)
                                    (
                                        $record->status === 'regional_head_review_pending' ||
                                        !blank($record->regional_head_review_data)
                                    )
                            )
                            ->schema([
                                Section::make('Regional Head Review')
                                    ->description('To be filled by the Regional Head')
                                    ->schema([
                                        ToggleButtons::make('regional_head_review_data.agree_with_chapter_head')
                                            ->label(self::styledLabel('1', 'Do you agree with the assessment made by the Chapter Head?'))
                                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                                            ->columns(8)
                                            ->colors(['Yes' => 'success', 'No' => 'danger'])
                                            ->icons(['Yes' => 'heroicon-o-hand-thumb-up', 'No' => 'heroicon-o-hand-thumb-down'])
                                            ->required()
                                            ->live()
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'regional_head_review_pending' && $record->pending_with === Auth::user()->employee?->employee_code)),

                                        RichEditor::make('regional_head_review_data.disagreement') // Renamed to match Infolist
                                            ->label(self::styledLabel('', 'Outline the Disagreements'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            // Visibility: Show only if "No" is selected. 
                                            // (Remove this line if you want the Comments box to be visible always)
                                            ->visible(fn(Get $get) => $get('regional_head_review_data.agree_with_chapter_head') === 'No')
                                            ->required(fn(Get $get) => $get('regional_head_review_data.agree_with_chapter_head') === 'No')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('regional_head_review_data.comments')
                                            ->label(self::styledLabel('2', 'Overall assessment'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->dehydrated()
                                            ->disabled(fn($record) => !($record->status === 'regional_head_review_pending' && $record->pending_with === Auth::user()->employee?->employee_code)),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color:rgb(83, 87, 82); padding: 0.25rem; border-radius: 0.5rem;']),

                            ]),

                        // --- Section 6: Part D - Final Assessment ---
                        Tabs\Tab::make('Final Assessment')
                            ->id('final-assessment')
                            ->icon('heroicon-m-check-badge')
                            ->visible(fn($record) => Auth::user()->hasRole('DG & CEO'))
                            ->schema([
                                Section::make('Regional Head Review')
                                    ->description('To be filled by the Regional Head')
                                    ->schema([
                                        ToggleButtons::make('final_assessment_data.agree_with_evaluation')
                                            ->label(self::styledLabel('1', 'Do you agree with the assessment?'))
                                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                                            ->columns(8)
                                            ->colors(['Yes' => 'success', 'No' => 'danger'])
                                            ->icons(['Yes' => 'heroicon-o-hand-thumb-up', 'No' => 'heroicon-o-hand-thumb-down'])
                                            ->required()
                                            ->live()
                                            ->dehydrated(),

                                        RichEditor::make('final_assessment_data.disagreement') // Renamed to match Infolist
                                            ->label(self::styledLabel('', 'Outline the Disagreements'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            // Visibility: Show only if "No" is selected. 
                                            // (Remove this line if you want the Comments box to be visible always)
                                            ->visible(fn(Get $get) => $get('final_assessment_data.agree_with_evaluation') === 'No')
                                            ->required(fn(Get $get) => $get('final_assessment_data.agree_with_evaluation') === 'No')
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        RichEditor::make('final_assessment_data.comments') // Renamed to match Infolist
                                            ->label(self::styledLabel('2', 'Overall assessment'))
                                            ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['bulletList', 'orderedList'], ['table']])
                                            ->dehydrated()
                                            ->columnSpanFull(),

                                        ToggleButtons::make('final_increment')
                                            ->label(self::styledLabel('3', 'Final Recommendation for Annual Increment'))
                                            ->options(['0%' => '0%', '5%' => '5%', '7%' => '7%', '10%' => '10%'])
                                            ->colors(['0%' => 'danger', '5%' => 'warning', '7%' => 'success', '10%' => 'success'])
                                            ->inline()
                                            ->required()
                                            ->dehydrated(),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color:rgb(33, 102, 17); padding: 0.25rem; border-radius: 0.5rem;']),
                            ])
                    ])
            ]);
    }
    /**
     * Helper to create rating dropdowns.
     */
    protected static function rating($key, $label)
    {
        return Slider::make("evaluation_form_data.ratings.{$key}")
            ->label($label)
            ->minValue(1)
            ->maxValue(10)
            ->step(0.5)
            ->decimalPlaces(1)
            ->pips(PipsMode::Values, density: 5)
            ->pipsValues([1, 2, 3, 4, 5, 6, 7, 8, 9, 10])
            ->tooltips()
            ->fillTrack()
            ->live() // Essential for real-time updates

            // --- NEW: Update the average field whenever this slider changes ---
            ->afterStateUpdated(fn(Get $get, Set $set) => $set('average_rating_display', self::calculateAverage($get)))

            ->extraAttributes(['style' => 'margin-left: 3rem; margin-right: 3rem; margin-bottom: 5rem;'])
            ->formatStateUsing(fn($state) => $state ? round($state, 1) : 1)
            ->dehydrated();
    }

    protected static function calculateAverage($get): string
    {
        // 1. Get the raw array of ratings from the form state
        // The model's method knows exactly which keys to look for, so we can pass the whole array safely.
        $ratings = $get('evaluation_form_data.ratings') ?? [];

        // 2. Use the centralized logic
        $score = \App\Models\Appraisal::calculateCompetencyScore($ratings);

        return number_format($score, 2) . ' / 10';
    }
}
