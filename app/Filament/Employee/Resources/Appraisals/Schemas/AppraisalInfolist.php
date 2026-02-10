<?php

namespace App\Filament\Employee\Resources\Appraisals\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;
use Filament\Schemas\Components\Tabs;

class AppraisalInfolist
{
    protected static function styledLabel(string $number, string $text): HtmlString
    {
        return new HtmlString("
        <span style='color: #2563eb; font-weight: 800; font-size: 1.1em; margin-right: 0.25rem;'>{$number}.</span>
        <span style='font-weight: 600; color: #2563eb; font-size: 1.1em;'>{$text}</span>
    ");
    }

    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- Header Section ---
                Tabs::make('AppraisalDetails')
                    ->columnSpanFull()
                    ->tabs([
                        // TAB 1: Employee Details & Appraisal Info
                        Tabs\Tab::make('Appraisal Details')
                            ->icon('heroicon-m-information-circle')
                            ->schema([
                                TextEntry::make('application_no')
                                    ->label('Appraisal Ref No'),
                                TextEntry::make('status')
                                    ->label('Current Status')
                                    ->badge()
                                    ->color(fn($record) => $record->status_color)
                                    ->formatStateUsing(fn($state) => ucfirst(str_replace('_', ' ', $state))),
                                TextEntry::make('appraisal_year')->label('Appraisal Year'),
                                TextEntry::make('appraisal_month')->label('Appraisal Month'),
                            ])->columnSpanFull()
                            ->columns(4),

                        // --- Snapshot Section ---
                        Tabs\Tab::make('Employee Details')
                            ->icon('heroicon-m-user')
                            ->schema([
                                TextEntry::make('employee.employee_code')->label('Employee Code'),
                                TextEntry::make('employee.name')->label('Name'),
                                TextEntry::make('designation.designation')->label('Designation'),
                                TextEntry::make('department.department')->label('Department'),
                                TextEntry::make('office.office')->label('Office'),
                                // TextEntry::make('basic')->label('Basic Pay')->money('INR'),
                            ])->columnSpanFull()
                            ->columns(3),

                        // --- NEW TAB: FILE HISTORY ---
                        Tabs\Tab::make('File History')
                            ->icon('heroicon-m-clock')
                            ->schema([
                                RepeatableEntry::make('file_history')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('timestamp')
                                            ->label('Date & Time')
                                            ->icon('heroicon-m-calendar')
                                            ->formatStateUsing(fn ($state) => $state ? \Carbon\Carbon::parse($state)->format('d M Y') : '-'),
                                        TextEntry::make('action')
                                            ->label('Action Taken')
                                            ->badge()
                                            ->color(fn($state) => match ($state) {
                                                'Submitted' => 'warning',
                                                'Assessed' => 'success',
                                                'Regional Review' => 'gray',
                                                'Finalised' => 'success',
                                                default => 'gray',
                                            }),
                                        TextEntry::make('actor_name')
                                            ->label('Official')
                                            ->icon('heroicon-m-user'),

                                        // REMOVED 'pending_with' from here

                                        TextEntry::make('remarks')
                                            ->label('Remarks')
                                            ->columnSpan(2),
                                    ])
                                    ->columns(5) // Reduced columns
                                    ->columnSpanFull(),

                                // --- NEW: Pending With (Outside Repeater) ---
                                Section::make()
                                    ->schema([
                                        TextEntry::make('pendingWith.name')
                                            ->label('Currently Pending With')
                                            ->icon('heroicon-m-arrow-path')
                                            ->badge()
                                            ->color('warning')
                                            ->size('lg')
                                            ->weight('bold'),
                                    ])
                            ]),
                    ]),

                // --- PART A: Visible to Everyone ---
                Tabs::make('AppraisalProcess')
                    ->columnSpanFull()
                    ->tabs([
                        // TAB 1: SELF APPRAISAL
                        Tabs\Tab::make('Appraisal Form')
                            ->icon('heroicon-m-user')
                            ->schema([
                                Section::make('Appraisal Form')
                                    ->description('To be filled by the Employee')
                                    ->schema([
                                        TextEntry::make('appraisal_form_data.job_profile')
                                            ->label(self::styledLabel('1', 'Define your job profile'))
                                            ->html()
                                            ->prose()
                                            // USE STYLE INSTEAD OF CLASS
                                            ->extraAttributes(['style' => 'border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;']),

                                        TextEntry::make('appraisal_form_data.job_satisfaction')
                                            ->label(self::styledLabel('2', 'How satisfied are you with your job profile?'))
                                            ->badge() // <--- This turns the text into a "Button" look
                                            ->icon(fn(string $state): ?string => match ($state) {
                                                'Not Satisfied'      => 'heroicon-o-hand-thumb-down',
                                                'Somewhat Satisfied' => 'heroicon-o-face-frown',
                                                'Satisfied'          => 'heroicon-o-face-smile',
                                                'Extremely Satisfied' => 'heroicon-o-hand-thumb-up',
                                                default              => null,
                                            })
                                            ->color(fn(string $state): string => match ($state) {
                                                'Not Satisfied'      => 'danger', // Matches your Form color
                                                'Somewhat Satisfied' => 'warning',
                                                'Satisfied'          => 'success',
                                                'Extremely Satisfied' => 'primary',
                                                default              => 'gray',
                                            })
                                            // OPTIONAL: Use extraAttributes to make it larger and squarer (more like a form button)
                                            ->extraAttributes([
                                                'class' => 'text-sm font-medium px-3 py-1 rounded-md' // rounded-md looks more like a button than the default pill
                                            ])
                                            // Maintain your existing styling
                                            ->extraAttributes(['style' => 'border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;']),

                                        TextEntry::make('appraisal_form_data.job_enrichment')
                                            ->label(self::styledLabel('3', 'Outline what you value most about your job profile and / or changes, 
                                            if any, that could help better utilise your potential within the Federation'))
                                            ->html()
                                            ->prose()
                                            ->extraAttributes(['style' => 'border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;']),

                                        TextEntry::make('appraisal_form_data.achievements')
                                            ->label(self::styledLabel('4', 'What were your achievements during the review period and how did you achieve them?'))
                                            ->html()
                                            ->prose()
                                            ->extraAttributes(['style' => 'border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;']),

                                        TextEntry::make('appraisal_form_data.performance_gaps')
                                            ->label(self::styledLabel('5', 'What areas of your individual performance could have been better during the review period?
                                            What support is required to improve the performance?'))
                                            ->html()
                                            ->prose()
                                            ->extraAttributes(['style' => 'border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;']),

                                        TextEntry::make('appraisal_form_data.career_goals')
                                            ->label(self::styledLabel('6', 'What are your medium to long-term career goals? How can the Federation help you to achieve them?'))
                                            ->html()
                                            ->prose()
                                            ->extraAttributes(['style' => 'border-bottom: 1px solid #e5e7eb; padding-bottom: 1.5rem; margin-bottom: 1.5rem;']),

                                        // The last item usually does not need a border, but you can add it if you prefer consistency
                                        TextEntry::make('appraisal_form_data.training_needs')
                                            ->label(self::styledLabel('7', 'Outline specific training and mentoring programs which would improve your performance 
                                            and make you more relevant and valuable to the Federation.'))
                                            ->html()
                                            ->prose(),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color: #2563eb; padding: 0.25rem; border-radius: 0.5rem;']),
                            ]),

                        // --- PART B: Confidential (Hidden from Employee) ---
                        Tabs\Tab::make('Evaluation Form')
                            ->icon('heroicon-m-clipboard-document-check')
                            ->visible(
                                fn($record) => (
                                    // Condition 1: Must have a Supervisor Role AND NOT be the Employee itself
                                    Auth::user()->hasAnyRole(['HOD', 'Regional Head', 'Chapter Head', 'DG & CEO'])
                                    && Auth::user()->employee?->id !== $record->employee_id // <--- CRITICAL FIX
                                )
                                    ||
                                    // Condition 2: OR the appraisal is released (visible to everyone)
                                    $record->is_released
                            )
                            ->schema([
                                Section::make('Evaluation Form')
                                    ->description('To be filled by the Supervising Officer')
                                    ->schema([
                                        TextEntry::make('evaluation_form_data.agree_with_employee')
                                            ->label(self::styledLabel('1', 'Do you agree with the information submitted by the employee in the Appraisal Form?'))
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('evaluation_form_data.disagreement')
                                            ->label(self::styledLabel('', 'Outline the Disagreements'))
                                            ->visible(fn($get) => $get('evaluation_form_data.agree_with_employee') === 'No')
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('evaluation_form_data.competency_comparison')
                                            ->label(self::styledLabel('2', 'Draw a comparison of the employee\'s job competencies vis-a-vis others with the same job profile'))
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('evaluation_form_data.initiative')
                                            ->label(self::styledLabel('3', 'Enumerate the employee\'s drive to take initiative and innovation'))
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('evaluation_form_data.accomplishments')
                                            ->label(self::styledLabel('4', 'Outline the employee\'s Outstanding accomplishments during the review period'))
                                            ->html()
                                            ->prose(),
                                        Section::make('Skills and Competency Rating (1-10)')
                                            ->label(self::styledLabel('5', 'Employee Skills and Competency Score'))
                                            ->description('Objectively rate each competency on a scale of 1–10 (1 = poor, 10 = excellent). The average score must be at least 3.0')
                                            ->schema([
                                                TextEntry::make('evaluation_form_data.ratings.knowledge')
                                                    ->label('Subject Knowledge')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.verbal_skills')
                                                    ->label('Verbal Skills')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.written_skills')
                                                    ->label('Written Skills')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.computer_skills')
                                                    ->label('IT Skills')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.teamwork')
                                                    ->label('Teamwork and Collaboration')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.discipline')
                                                    ->label('Self-Discipline and Work Ethics')
                                                    ->badge(),
                                                // TextEntry::make('evaluation_form_data.ratings.relationships')
                                                //     ->label('Interpersonal Relations')
                                                //     ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.obedience')
                                                    ->label('Obedience and Insurbordination')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.planning')
                                                    ->label('Planning and Time Management')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.responsibility')
                                                    ->label('Shoulder Additional Responsibilities')
                                                    ->badge(),
                                                TextEntry::make('evaluation_form_data.ratings.adaptability')
                                                    ->label('Adaptability and Flexibility')
                                                    ->badge(),
                                                // TextEntry::make('evaluation_form_data.ratings.leadership')
                                                //     ->label('Leadership Qualities')
                                                //     ->badge(),

                                                // --- NEW: AVERAGE DISPLAY ---
                                                TextEntry::make('average_rating')
                                                    ->label('Average Score')
                                                    ->state(function ($record) {
                                                        $ratings = $record->evaluation_form_data['ratings'] ?? [];

                                                        $keys = [
                                                            'knowledge',
                                                            'verbal_skills',
                                                            'written_skills',
                                                            'computer_skills',
                                                            'teamwork',
                                                            'discipline',
                                                            'relationships',
                                                            'obedience',
                                                            'planning',
                                                            'responsibility',
                                                            'adaptability',
                                                            'leadership'
                                                        ];

                                                        $sum = 0;
                                                        $count = 0;

                                                        foreach ($keys as $key) {
                                                            if (isset($ratings[$key]) && is_numeric($ratings[$key])) {
                                                                $sum += $ratings[$key];
                                                                $count++;
                                                            }
                                                        }

                                                        return $count > 0
                                                            ? number_format($sum / $count, 2)
                                                            : '0.00';
                                                    })
                                                    ->badge()
                                                    ->color('info')
                                                    ->size('lg')
                                                    ->columnSpanFull(), // Make it stand out at the bottom
                                            ])
                                            ->columns(5),
                                        TextEntry::make('evaluation_form_data.overall_assessment')
                                            ->label(self::styledLabel('6', 'Overall Assessment'))
                                            ->html()
                                            ->prose(),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color:rgb(33, 102, 17); padding: 0.25rem; border-radius: 0.5rem;']),
                            ]),

                        // ... inside the components array ...

                        // --- PART C: Regional Head Assessment ---
                        Tabs\Tab::make('Regional Head Review')
                            ->icon('heroicon-m-building-office')
                            ->visible(
                                fn($record) =>
                                // 1. Permissions Check
                                (Auth::user()->hasAnyRole(['Regional Head', 'DG & CEO']) || $record->is_released) &&
                                    // 2. Status Check
                                    in_array($record->status, ['regional_head_review_pending', 'final_assessment_pending', 'closed']) // &&
                                    // 3. CRITICAL FIX: Only show if the data is actually there. 
                                    // If skipped (empty), hide the section entirely.
                                    // !blank($record->regional_head_review_data)
                            )
                            ->schema([
                                Section::make('Regional Head Review')
                                    ->description('To be filled by the Regional Head')
                                    ->schema([
                                        TextEntry::make('regional_head_review_data.agree_with_chapter_head')
                                            ->label(self::styledLabel('1', 'Do you agree with the assessment made by the Chapter Head?'))
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('regional_head_review_data.disagreement')
                                            ->label(self::styledLabel('', 'Outline the Disagreements'))
                                            ->visible(fn($get) => $get('regional_head_review_data.agree_with_chapter_head') === 'No')
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('regional_head_review_data.comments')
                                            ->label(self::styledLabel('2', 'Overall assessment'))
                                            ->html()
                                            ->prose(),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color:rgb(83, 87, 82); padding: 0.25rem; border-radius: 0.5rem;']),
                            ]),

                        // --- PART D: Final Assessment (DG Only) ---
                        Tabs\Tab::make('Final Assessment')
                            ->icon('heroicon-m-check-badge')
                            ->visible(fn($record) => Auth::user()->hasRole('DG & CEO'))
                            ->schema([
                                Section::make('Final Assessment')
                                    ->description('To be filled by the DG & CEO')
                                    ->schema([
                                        TextEntry::make('final_assessment_data.agree_with_evaluation')
                                            ->label(self::styledLabel('1', 'Do you agree with the assessment?'))
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('final_assessment_data.disagreement')
                                            ->label(self::styledLabel('2', 'Outline the Disagreements'))
                                            ->visible(fn($get) => $get('final_assessment_data.agree_with_evaluation') === 'No')
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('final_assessment_data.comments')
                                            ->label(self::styledLabel('2', 'Overall assessment'))
                                            ->html()
                                            ->prose(),
                                        TextEntry::make('final_increment')
                                            ->label(self::styledLabel('3', 'Final Recommendation for Annual Increment'))
                                            ->html()
                                            ->prose()
                                            ->badge()
                                            ->color('success'),
                                    ])->columnSpanFull()
                                    ->extraAttributes(['style' => 'background-color:rgb(102, 51, 153); padding: 0.25rem; border-radius: 0.5rem;']),

                            ])
                    ])
            ]);
    }
}
