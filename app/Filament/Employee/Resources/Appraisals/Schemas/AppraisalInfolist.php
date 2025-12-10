<?php

namespace App\Filament\Employee\Resources\Appraisals\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Illuminate\Support\Facades\Auth;

class AppraisalInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // --- Header Section ---
                Section::make('Appraisal Details')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('application_no'),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn ($record) => $record->status_color)
                            ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),
                        TextEntry::make('appraisal_year')->label('Year'),
                        TextEntry::make('appraisal_cycle')->label('Cycle'),
                    ])->columnSpanFull(),

                // --- Snapshot Section ---
                Section::make('Employee Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('employee.employee_code')->label('Employee Code'),
                        TextEntry::make('employee.name')->label('Name'),
                        TextEntry::make('designation.designation')->label('Designation'),
                        TextEntry::make('department.department')->label('Department'),
                        TextEntry::make('office.office')->label('Office'),
                        TextEntry::make('basic')->label('Basic Pay')->money('INR'),
                    ])->columnSpanFull(),

                // --- PART A: Visible to Everyone ---
                Section::make('Appraisal Form (Part A)')
                    ->description('Employee Self Appraisal')
                    ->schema([
                        TextEntry::make('appraisal_form_data.job_profile')->label('Job Profile'),
                        TextEntry::make('appraisal_form_data.satisfaction')->label('Satisfaction'),
                        TextEntry::make('appraisal_form_data.profile_modifications')->label('Profile Modifications'),
                        TextEntry::make('appraisal_form_data.achievements')->label('Achievements'),
                        TextEntry::make('appraisal_form_data.performance_gaps')->label('Performance Gaps'),
                        TextEntry::make('appraisal_form_data.career_goals')->label('Career Goals'),
                        TextEntry::make('appraisal_form_data.training_needs')->label('Training Needs'),
                    ])->columnSpanFull(),

                // --- PART B: Confidential (Hidden from Employee) ---
                Section::make('Common Evaluation (Part B)')
                    ->description('Confidential Evaluation by Reporting Officer')
                    ->visible(fn ($record) => 
                        Auth::user()->hasAnyRole(['HOD', 'Regional Head', 'Chapter Head', 'DG & CEO']) || 
                        $record->is_released
                    )
                    ->schema([
                        TextEntry::make('common_evaluation_data.agree_with_employee')->label('Agrees with Employee?'),
                        TextEntry::make('common_evaluation_data.competency_comparison')->label('Competency Comparison'),
                        TextEntry::make('common_evaluation_data.initiative')->label('Initiative'),
                        TextEntry::make('common_evaluation_data.accomplishments')->label('Accomplishments'),
                        TextEntry::make('common_evaluation_data.overall_assessment')->label('Overall Assessment'),
                    ])->columnSpanFull(),

                // --- PART C: Regional Head (Hidden from Employee & Chapter Head) ---
                Section::make('Regional Head Assessment')
                    ->visible(fn ($record) => 
                        Auth::user()->hasAnyRole(['Regional Head', 'DG & CEO']) || 
                        $record->is_released
                    )
                    ->schema([
                        TextEntry::make('regional_head_assessment_data.agree_with_chapter_head')->label('Agrees with Chapter Head?'),
                        TextEntry::make('regional_head_assessment_data.comments')->label('Comments'),
                    ])->columnSpanFull(),

                // --- PART D: Final Assessment (DG Only) ---
                Section::make('Final Assessment')
                    ->visible(fn ($record) => Auth::user()->hasRole('DG & CEO'))
                    ->schema([
                        TextEntry::make('final_assessment_data.agree_with_evaluation')->label('Agrees with Evaluation?'),
                        TextEntry::make('final_increment')
                            ->label('Final Increment %')
                            ->badge()
                            ->color('success'),
                    ])->columnSpanFull(),
            ]);
    }
}