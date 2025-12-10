<?php

namespace App\Filament\Employee\Resources\Appraisals\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;

class AppraisalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- 1. Header & Snapshot Data ---
                Section::make('Appraisal Details')
                    ->columns(4)
                    ->schema([
                        TextInput::make('application_no')
                            ->label('Appraisal No')
                            ->placeholder('Auto-generated')
                            ->disabled()
                            ->dehydrated(),

                        // FIX: Allow null state and handle default for 'Create' view
                        TextInput::make('status')
                            ->default('draft')
                            ->formatStateUsing(fn(?string $state): string => ucfirst(str_replace('_', ' ', $state ?? 'draft')))
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('appraisal_year')
                            ->label('Appraisal Year')
                            ->default(date('Y'))
                            ->disabled()
                            ->dehydrated(),

                        TextInput::make('appraisal_cycle')
                            ->label('Appraisal Month')
                            ->default('April') // Or determine dynamically
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columnspanfull(),
                Section::make('Employee Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employee_code')
                            ->label('Employee Code')
                            ->dehydrated(false)
                            ->default(fn() => Auth::user()->employee?->employee_code)
                            ->disabled(),

                        TextInput::make('employee_name')
                            ->label('Name')
                            ->dehydrated(false)
                            // FIX: Load from relationship if record exists, else use Auth user
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->name ?? Auth::user()->employee?->name))
                            ->disabled(),

                        TextInput::make('designation_name')
                            ->label('Designation')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->designation?->designation ?? Auth::user()->employee?->designation?->designation))
                            ->disabled(),

                        TextInput::make('department_name')
                            ->label('Department')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->department?->department ?? Auth::user()->employee?->department?->department))
                            ->disabled(),

                        TextInput::make('office_name')
                            ->label('Office')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->office?->office ?? Auth::user()->employee?->office?->office))
                            ->disabled(),

                        TextInput::make('basic')
                            ->label('Basic Pay')
                            ->dehydrated(false)
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->basic ?? Auth::user()->employee?->basic))
                            ->disabled(),
                    ])->columnspanfull(),

                // --- 2. APPRAISAL FORM (Part A) ---
                Section::make('Appraisal Form')
                    ->description('To be filled by the Employee')
                    ->disabled(
                        fn($record) =>
                        $record && (
                            $record->status !== 'draft' ||
                            $record->employee_code !== Auth::user()->employee?->employee_code
                        )
                    )
                    ->schema([
                        Textarea::make('appraisal_form_data.job_profile')
                            ->label('1. Define your job profile')
                            ->rows(3)
                            ->required(),

                        ToggleButtons::make('appraisal_form_data.satisfaction')
                            ->label('2. Are you satisfied with your job profile?')
                            ->options([
                                'Not Satisfied' => 'Not Satisfied',
                                'Somewhat Satisfied' => 'Somewhat Satisfied',
                                'Satisfied' => 'Satisfied',
                                'Extremely Satisfied' => 'Extremely Satisfied',
                            ])
                            ->inline()
                            ->required(),

                        Textarea::make('appraisal_form_data.profile_modifications')
                            ->label('3. What can be modified in your job profile?')
                            ->rows(2),

                        Textarea::make('appraisal_form_data.achievements')
                            ->label('4. Achievements during review period')
                            ->rows(4)
                            ->required(),

                        Textarea::make('appraisal_form_data.performance_gaps')
                            ->label('5. Areas for improvement & support required')
                            ->rows(2),

                        Textarea::make('appraisal_form_data.career_goals')
                            ->label('6. Medium to long-term career goals')
                            ->rows(2),

                        Textarea::make('appraisal_form_data.training_needs')
                            ->label('7. Specific training/mentoring required')
                            ->rows(2),
                    ])->columnspanfull(),

                // --- 3. COMMON EVALUATION (Part B) ---
                Section::make('Common Evaluation (Part B)')
                    ->description('To be filled by Reporting Officer')
                    ->visible(
                        fn($record) =>
                        Auth::user()->hasAnyRole(['HOD', 'Regional Head', 'Chapter Head', 'DG & CEO']) &&
                            $record && $record->status !== 'draft'
                    )
                    ->disabled(
                        fn($record) =>
                        $record && $record->pending_with !== Auth::user()->employee?->employee_code
                    )
                    ->schema([
                        Radio::make('common_evaluation_data.agree_with_employee')
                            ->label('Do you agree with the information given by the employee?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),

                        Textarea::make('common_evaluation_data.competency_comparison')
                            ->label('Job Competencies vis-a-vis others')
                            ->rows(2),

                        Textarea::make('common_evaluation_data.initiative')
                            ->label('Drive to take initiative and innovation')
                            ->rows(2),

                        Textarea::make('common_evaluation_data.accomplishments')
                            ->label('Outstanding accomplishments')
                            ->rows(2),

                        Section::make('Core Competencies Rating (1-5)')
                            ->columns(2)
                            ->schema([
                                self::rating('knowledge', 'Knowledge'),
                                self::rating('verbal_skills', 'Verbal Skills'),
                                self::rating('written_skills', 'Written Skills'),
                                self::rating('computer_skills', 'Computer Skills'),
                                self::rating('teamwork', 'Teamwork'),
                                self::rating('discipline', 'Discipline'),
                                self::rating('mentoring', 'Mentoring'),
                                self::rating('relationships', 'Interpersonal Relationships'),
                                self::rating('anticipate_issues', 'Anticipate & Address Issues'),
                                self::rating('planning', 'Planning & Time Management'),
                            ]),

                        Textarea::make('common_evaluation_data.overall_assessment')
                            ->label('Overall Assessment')
                            ->rows(3)
                            ->required(),
                    ])->columnspanfull(),

                // --- 4. REGIONAL HEAD ASSESSMENT ---
                Section::make('Assessment by Regional Head')
                    ->description('For Staff reporting to Chapter Heads')
                    ->visible(
                        fn($record) =>
                        $record &&
                            in_array($record->status, ['regional_head_review_pending', 'final_review_pending', 'closed']) &&
                            Auth::user()->hasAnyRole(['Regional Head', 'DG & CEO'])
                    )
                    ->disabled(
                        fn($record) =>
                        $record && $record->pending_with !== Auth::user()->employee?->employee_code
                    )
                    ->schema([
                        Radio::make('regional_head_assessment_data.agree_with_chapter_head')
                            ->label('Do you agree with the assessment made by the Chapter Head?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),

                        Textarea::make('regional_head_assessment_data.comments')
                            ->label('Comments')
                            ->rows(3),
                    ])->columnspanfull(),

                // --- 5. FINAL ASSESSMENT (DG & CEO) ---
                Section::make('Final Assessment')
                    ->visible(fn($record) => Auth::user()->hasRole('DG & CEO'))
                    ->disabled(
                        fn($record) =>
                        $record && $record->pending_with !== Auth::user()->employee?->employee_code
                    )
                    ->schema([
                        Radio::make('final_assessment_data.agree_with_evaluation')
                            ->label('Do you agree with the assessment?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required(),

                        Textarea::make('final_assessment_data.disagreement_details')
                            ->label('Details of disagreement (if any)')
                            ->visible(fn(Get $get) => $get('final_assessment_data.agree_with_evaluation') === 'No'),

                        ToggleButtons::make('final_increment')
                            ->label('Final Recommendation (Annual Increment)')
                            ->options([
                                '0%' => '0%',
                                '3%' => '3%',
                                '5%' => '5%',
                                '7%' => '7%'
                            ])
                            ->colors([
                                '0%' => 'danger',
                                '3%' => 'warning',
                                '5%' => 'success',
                                '7%' => 'success'
                            ])
                            ->inline()
                            ->required(),
                    ])->columnspanfull(),
            ]);
    }

    protected static function rating($key, $label)
    {
        return Select::make("common_evaluation_data.ratings.{$key}")
            ->label($label)
            ->options([1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'])
            ->native(false);
    }
}
