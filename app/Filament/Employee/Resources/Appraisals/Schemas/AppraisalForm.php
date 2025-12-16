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
use Filament\Forms\Components\Placeholder;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Forms\Components\Grid;

class AppraisalForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // ... (Section 1: Header - KEEP AS IS) ...
                Section::make('Appraisal Details')
                    ->columns(4)
                    ->schema([
                        TextInput::make('application_no')->label('Appraisal Ref No')->placeholder('Auto-generated')->disabled()->dehydrated(),
                        TextInput::make('status')->label('Current Status')->default('draft')->formatStateUsing(fn(?string $state): string => ucfirst(str_replace('_', ' ', $state ?? 'draft')))->disabled()->dehydrated(false),
                        TextInput::make('appraisal_year')->label('Appraisal Year')->default(date('Y'))->disabled()->dehydrated(),
                        TextInput::make('appraisal_cycle')->label('Appraisal Month')->default('April')->disabled()->dehydrated(),
                    ])->columnSpanFull(),

                // ... (Section: Employee Details - KEEP AS IS) ...
                Section::make('Employee Details')
                    ->columns(3)
                    ->schema([
                        TextInput::make('employee_code')->label('Employee Code')->dehydrated()->disabled()
                            ->default(fn() => Auth::user()->employee?->employee_code)
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee_code ?? Auth::user()->employee?->employee_code)),

                        TextInput::make('employee_name')->label('Name')->dehydrated(false)->disabled()
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->employee?->name ?? Auth::user()->employee?->name)),

                        TextInput::make('designation_name')->label('Designation')->dehydrated(false)->disabled()
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->designation?->designation ?? Auth::user()->employee?->designation?->designation)),

                        TextInput::make('department_name')->label('Department')->dehydrated(false)->disabled()
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->department?->department ?? Auth::user()->employee?->department?->department)),

                        TextInput::make('office_name')->label('Office')->dehydrated(false)->disabled()
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->office?->office ?? Auth::user()->employee?->office?->office)),

                        TextInput::make('basic')->label('Basic Pay')->dehydrated()->disabled()
                            ->default(fn() => Auth::user()->employee?->basic)
                            ->afterStateHydrated(fn($component, $record) => $component->state($record?->basic ?? Auth::user()->employee?->basic)),

                        Hidden::make('employee_id')->default(fn() => Auth::user()->employee?->id),
                    ])->columnSpanFull(),

                // ... (Section 2: Part A - Employee - KEEP THE PLACEHOLDER LOGIC I GAVE YOU) ...
                Section::make('Appraisal Form')
                    ->description('To be filled by the Employee')
                    ->schema([
                        // ... (Paste the Part A code I gave you in the previous step here) ...
                        // Ensure it uses the visible() toggle between RichEditor and Placeholder
                        // For brevity, I am not repeating it here, but KEEP IT exactly as the previous solution.
                        RichEditor::make('appraisal_form_data.job_profile')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('1. Define your job profile')
                            ->required()
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_job_profile')
                            ->label('1. Define your job profile')
                            ->content(fn($record) => new HtmlString($record->appraisal_form_data['job_profile'] ?? '-'))
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),

                        // ... (Repeat for all Part A fields) ...
                        ToggleButtons::make('appraisal_form_data.job_satisfaction')
                            ->label('2. How satisfied are you with your job profile?')
                            ->options([
                                'Not Satisfied' => 'Not Satisfied',
                                'Somewhat Satisfied' => 'Somewhat Satisfied',
                                'Satisfied' => 'Satisfied',
                                'Extremely Satisfied' => 'Extremely Satisfied',
                            ])
                            ->columns([
                                'default' => 1, // Stack vertically on mobile
                                'sm' => 2,      // 2 columns on small screens
                                'lg' => 4,      // 4 columns on large screens (equal width)
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
                            ->required()
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_job_satisfaction')
                            ->label('2. How satisfied are you with your job profile?')
                            ->content(fn($record) => $record->appraisal_form_data['job_satisfaction'] ?? '-')
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),

                        RichEditor::make('appraisal_form_data.job_enrichment')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('3. What can be modified in your job profile or in which area of functioning you may be deployed to utilize your potential much more effectively?')
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_job_enrichment')
                            ->label('3. What can be modified in your job profile or in which area of functioning you may be deployed to utilize your potential much more effectively?')
                            ->content(fn($record) => new HtmlString($record->appraisal_form_data['job_enrichment'] ?? '-'))
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),

                        RichEditor::make('appraisal_form_data.achievements')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('4. What were your achievements during the review period? How did you achieve it?')
                            ->required()
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_achievements')
                            ->label('4. What were your achievements during the review period? How did you achieve it?')
                            ->content(fn($record) => new HtmlString($record->appraisal_form_data['achievements'] ?? '-'))
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),

                        RichEditor::make('appraisal_form_data.performance_gaps')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('5. What are the areas where performance could have been better?  What support is required to improve the performance?')
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_performance_gaps')
                            ->label('5. What are the areas where performance could have been better?  What support is required to improve the performance?')
                            ->content(fn($record) => new HtmlString($record->appraisal_form_data['performance_gaps'] ?? '-'))
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),

                        RichEditor::make('appraisal_form_data.career_goals')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('6. What are your medium to long-term career goals?  How can be Federation help you to achieve the same?')
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_career_goals')
                            ->label('6. What are your medium to long-term career goals?  How can be Federation help you to achieve the same?')
                            ->content(fn($record) => new HtmlString($record->appraisal_form_data['career_goals'] ?? '-'))
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),

                        RichEditor::make('appraisal_form_data.training_needs')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('7. Outline specific training, mentoring programs which would improve your performance and make you more relevant and valuable to the Federation.')
                            ->visible(fn($record) => $record === null || $record->status === 'draft')
                            ->columnSpanFull(),

                        Placeholder::make('view_training_needs')
                            ->label('7. Outline specific training, mentoring programs which would improve your performance and make you more relevant and valuable to the Federation.')
                            ->content(fn($record) => new HtmlString($record->appraisal_form_data['training_needs'] ?? '-'))
                            ->visible(fn($record) => $record && $record->status !== 'draft')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                // --- 3. COMMON EVALUATION (Part B) ---
                // LOGIC: Editable if 'submitted'. Read-Only if anything else (like 'regional_head_review_pending').
                Section::make('Common Evaluation Form')
                    ->description('To be filled by Reporting Officer')
                    ->visible(
                        fn($record) =>
                        Auth::user()->hasAnyRole(['HOD', 'Regional Head', 'Chapter Head', 'DG & CEO']) &&
                            $record &&
                            $record->status !== 'draft' &&
                            Auth::user()->employee?->id !== $record->employee_id // <--- CRITICAL FIX
                    )
                    ->disabled(
                        fn($record) =>
                        $record && $record->pending_with !== Auth::user()->employee?->employee_code
                    )
                    ->schema([
                        // *** EDIT MODE COMPONENTS (Visible only when Submitted) ***
                        ToggleButtons::make('common_evaluation_data.agree_with_employee')
                            ->label('Do you agree with the information given by the employee?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->columns(8)
                            ->colors(['Yes' => 'success', 'No' => 'danger'])
                            ->icons(['Yes' => 'heroicon-o-hand-thumb-up', 'No' => 'heroicon-o-hand-thumb-down'])
                            ->required()
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code),

                        // *** READ-ONLY MODE COMPONENTS (Visible when Review Pending or Closed) ***
                        Placeholder::make('view_agree_with_employee')
                            ->label('Do you agree with the information given by the employee?')
                            ->content(fn($record) => $record->common_evaluation_data['agree_with_employee'] ?? '-')
                            ->visible(fn($record) => $record->status !== 'submitted'),

                        // --- Competency Comparison ---
                        RichEditor::make('common_evaluation_data.competency_comparison')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('Job Competencies vis-a-vis others')
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code),

                        Placeholder::make('view_competency_comparison')
                            ->label('Job Competencies vis-a-vis others')
                            ->content(fn($record) => new HtmlString($record->common_evaluation_data['competency_comparison'] ?? '-'))
                            ->visible(fn($record) => $record->status !== 'submitted'),

                        // --- Initiative ---
                        RichEditor::make('common_evaluation_data.initiative')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('Drive to take initiative and innovation')
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code),

                        Placeholder::make('view_initiative')
                            ->label('Drive to take initiative and innovation')
                            ->content(fn($record) => new HtmlString($record->common_evaluation_data['initiative'] ?? '-'))
                            ->visible(fn($record) => $record->status !== 'submitted'),

                        // --- Accomplishments ---
                        RichEditor::make('common_evaluation_data.accomplishments')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('Outstanding accomplishments')
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code),

                        Placeholder::make('view_accomplishments')
                            ->label('Outstanding accomplishments')
                            ->content(fn($record) => new HtmlString($record->common_evaluation_data['accomplishments'] ?? '-'))
                            ->visible(fn($record) => $record->status !== 'submitted'),

                        // --- Ratings Section (Inputs vs View) ---
                        Section::make('Core Competencies Rating (1-5)')
                            ->columns(2)
                            ->visible(fn($record) => $record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code)
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

                        // --- Ratings View (For Reviewer) ---
                        Section::make('Core Competencies Rating (View)')
                            ->columns(2)
                            ->visible(fn($record) => $record->status !== 'submitted')
                            ->schema([
                                Placeholder::make('view_rating_knowledge')->label('Knowledge')->content(fn($record) => $record->common_evaluation_data['ratings']['knowledge'] ?? '-'),
                                // ... (Add placeholders for other ratings if needed, or just show overall) ...
                            ]),

                        // --- Overall Assessment ---
                        RichEditor::make('common_evaluation_data.overall_assessment')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('Overall Assessment')
                            ->required()
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'submitted' && $record->pending_with === Auth::user()->employee?->employee_code),

                        Placeholder::make('view_overall_assessment')
                            ->label('Overall Assessment')
                            ->content(fn($record) => new HtmlString($record->common_evaluation_data['overall_assessment'] ?? '-'))
                            ->visible(fn($record) => $record->status !== 'submitted'),

                    ])->columnSpanFull(),

                // --- 4. REGIONAL HEAD ASSESSMENT (Part C) ---
                Section::make('Assessment by Regional Head')
                    ->description('For Staff reporting to Chapter Heads')
                    ->visible(
                        fn($record) =>
                        $record &&
                            // Visible ONLY if status is Regional Review (or later)
                            // This hides it for RO Employees (status='submitted') -> Fulfilling your Condition 1
                            in_array($record->status, ['regional_head_review_pending', 'final_review_pending', 'closed']) &&
                            Auth::user()->hasAnyRole(['Regional Head', 'DG & CEO'])
                    )
                    ->schema([
                        // Editable Components (Only when status is 'regional_head_review_pending')
                        Radio::make('regional_head_assessment_data.agree_with_chapter_head')
                            ->label('Do you agree with the assessment made by the Chapter Head?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required()
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'regional_head_review_pending' && $record->pending_with === Auth::user()->employee?->employee_code),

                        RichEditor::make('regional_head_assessment_data.comments')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('Comments')
                            ->dehydrated()
                            ->visible(fn($record) => $record->status === 'regional_head_review_pending' && $record->pending_with === Auth::user()->employee?->employee_code),

                        // Read-Only Components (When passed to DG)
                        Placeholder::make('view_rh_agree')
                            ->label('Do you agree with the assessment made by the Chapter Head?')
                            ->content(fn($record) => $record->regional_head_assessment_data['agree_with_chapter_head'] ?? '-')
                            ->visible(fn($record) => $record->status !== 'regional_head_review_pending'),

                        Placeholder::make('view_rh_comments')
                            ->label('Comments')
                            ->content(fn($record) => new HtmlString($record->regional_head_assessment_data['comments'] ?? '-'))
                            ->visible(fn($record) => $record->status !== 'regional_head_review_pending'),
                    ])->columnSpanFull(),

                // --- 5. FINAL ASSESSMENT (DG & CEO) ---
                // (Keep as is, or apply similar placeholder logic if needed)
                Section::make('Final Assessment')
                    ->visible(fn($record) => Auth::user()->hasRole('DG & CEO'))
                    ->schema([
                        Radio::make('final_assessment_data.agree_with_evaluation')
                            ->label('Do you agree with the assessment?')
                            ->options(['Yes' => 'Yes', 'No' => 'No'])
                            ->required()
                            ->dehydrated(),

                        RichEditor::make('final_assessment_data.disagreement_details')
                            ->toolbarButtons([
                                ['italic', 'underline', 'link'],
                                ['bulletList', 'orderedList'],
                                ['table'], // The `customBlocks` and `mergeTags` tools are also added here if those features are used.

                            ])
                            ->label('Details of disagreement (if any)')
                            ->visible(fn(Get $get) => $get('final_assessment_data.agree_with_evaluation') === 'No')
                            ->dehydrated()
                            ->columnSpanFull(),

                        ToggleButtons::make('final_increment')
                            ->label('Final Recommendation (Annual Increment)')
                            ->options(['0%' => '0%', '5%' => '5%', '7%' => '7%', '10%' => '10%'])
                            ->colors(['0%' => 'danger', '5%' => 'warning', '7%' => 'success', '10%' => 'success'])
                            ->inline()
                            ->required()
                            ->dehydrated(),
                    ])->columnSpanFull(),
            ]);
    }

    protected static function rating($key, $label)
    {
        return Select::make("common_evaluation_data.ratings.{$key}")
            ->label($label)
            ->options([1 => '1 - Minimum', 2 => '2', 3 => '3', 4 => '4', 5 => '5 - Maximum'])
            ->native(false)
            ->dehydrated();
    }
}
