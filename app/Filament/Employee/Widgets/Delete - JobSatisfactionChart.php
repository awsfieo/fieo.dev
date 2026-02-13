<?php

namespace App\Filament\Employee\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use App\Models\Appraisal;

class JobSatisfactionChart extends ChartWidget
{
    protected ?string $heading = 'Employee Job Satisfaction';
    
    protected static ?int $sort = 5;

    public static function canView(): bool
    {
        // return Auth::user()->hasRole('DG & CEO');
        return false;
    }

    protected function getData(): array
    {
        // A. Initialize Counters
        $counts = [
            'Not Satisfied'      => 0,
            'Somewhat Satisfied' => 0,
            'Satisfied'          => 0,
            'Extremely Satisfied' => 0,
        ];

        // B. Fetch Data 
        // Note: We removed the strict Year filter temporarily to ensure data shows up. 
        // You can add ->where('appraisal_year', date('Y')) back later if needed.
        $appraisals = Appraisal::query()
            ->whereNotIn('status', ['draft']) // Exclude drafts
            ->get();

        // C. Process Data
        foreach ($appraisals as $appraisal) {
            // FIX: Access the key directly ('job_satisfaction'), not the full dot-notation string
            $data = $appraisal->appraisal_form_data;
            
            // Check if data exists and is an array before trying to access the key
            if (is_array($data)) {
                $response = $data['job_satisfaction'] ?? null;

                if ($response && array_key_exists($response, $counts)) {
                    $counts[$response]++;
                }
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Responses',
                    'data' => array_values($counts),
                    'backgroundColor' => [
                        '#ef4444', // Red (Not Satisfied)
                        '#f59e0b', // Amber (Somewhat)
                        '#3b82f6', // Blue (Satisfied)
                        '#22c55e', // Green (Extremely)
                    ],
                ],
            ],
            'labels' => array_keys($counts),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}