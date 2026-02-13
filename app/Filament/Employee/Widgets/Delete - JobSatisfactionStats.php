<?php

namespace App\Filament\Employee\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use App\Models\Appraisal;

class JobSatisfactionStats extends BaseWidget
{
    // 1. VISIBILITY: Only for DG & CEO
    public static function canView(): bool
    {
        // return Auth::user()->hasRole('DG & CEO');

        return false; // TEMP: Hide for now until we have data to show
    }

    protected static ?int $sort = 5; // Adjust position on dashboard

    protected function getStats(): array
    {
        // A. Initialize Counters
        $counts = [
            'Not Satisfied'      => 0,
            'Somewhat Satisfied' => 0,
            'Satisfied'          => 0,
            'Extremely Satisfied' => 0,
        ];

        // B. Fetch Data (Submitted forms only)
        // Note: You can add ->where('appraisal_year', date('Y')) if needed
        $appraisals = Appraisal::query()
            ->whereNotIn('status', ['draft']) 
            ->get();

        // C. Process Data
        foreach ($appraisals as $appraisal) {
            $data = $appraisal->appraisal_form_data;
            
            if (is_array($data)) {
                // Access the correct key from your form schema
                $response = $data['job_satisfaction'] ?? null;

                if ($response && array_key_exists($response, $counts)) {
                    $counts[$response]++;
                }
            }
        }

        // D. Return Stats Cards
        return [
            Stat::make('Not Satisfied', $counts['Not Satisfied'])
                ->description('Employees unhappy with profile')
                ->descriptionIcon('heroicon-m-hand-thumb-down')
                ->color('danger'), // Red

            Stat::make('Somewhat Satisfied', $counts['Somewhat Satisfied'])
                ->description('Room for improvement')
                ->descriptionIcon('heroicon-m-face-frown')
                ->color('warning'), // Orange

            Stat::make('Satisfied', $counts['Satisfied'])
                ->description('Meeting expectations')
                ->descriptionIcon('heroicon-m-face-smile')
                ->color('info'), // Blue

            Stat::make('Extremely Satisfied', $counts['Extremely Satisfied'])
                ->description('Highly engaged employees')
                ->descriptionIcon('heroicon-m-hand-thumb-up')
                ->color('success'), // Green
        ];
    }
}