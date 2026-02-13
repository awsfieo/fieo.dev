<?php

namespace App\Filament\Employee\Widgets;

use Filament\Widgets\Widget;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Actions\Action;
use Illuminate\Support\Facades\Auth;
use App\Models\Appraisal;

class JobSatisfactionStats extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected string $view = 'filament.employee.widgets.job-satisfaction-stats';

    protected static ?int $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return Auth::user()->hasRole('DG & CEO');
    }

    protected ?\Illuminate\Support\Collection $submittedAppraisals = null;

    protected function getSubmittedAppraisals()
    {
        return $this->submittedAppraisals ??= Appraisal::query()
            ->with('employee')
            ->whereNotIn('status', ['draft'])
            ->get();
    }

    public function getStats(): array
    {
        $counts = [
            'Not Satisfied'       => 0,
            'Somewhat Satisfied'  => 0,
            'Satisfied'           => 0,
            'Extremely Satisfied' => 0,
        ];

        foreach ($this->getSubmittedAppraisals() as $appraisal) {
            $data = $appraisal->appraisal_form_data;

            if (is_array($data)) {
                $response = $data['job_satisfaction'] ?? null;

                if ($response && array_key_exists($response, $counts)) {
                    $counts[$response]++;
                }
            }
        }

        return $counts;
    }

    public function getMeta(string $label): array
    {
        return match ($label) {
            'Not Satisfied'       => ['accent' => '#dc2626', 'tint' => 'rgba(220,38,38,.10)',  'icon' => 'heroicon-m-hand-thumb-down'],
            'Somewhat Satisfied'  => ['accent' => '#d97706', 'tint' => 'rgba(217,119,6,.12)',  'icon' => 'heroicon-m-face-frown'],
            'Satisfied'           => ['accent' => '#2563eb', 'tint' => 'rgba(37,99,235,.10)',  'icon' => 'heroicon-m-face-smile'],
            'Extremely Satisfied' => ['accent' => '#16a34a', 'tint' => 'rgba(22,163,74,.10)',  'icon' => 'heroicon-m-hand-thumb-up'],
            default               => ['accent' => '#6b7280', 'tint' => 'rgba(107,114,128,.10)', 'icon' => 'heroicon-m-question-mark-circle'],
        };
    }

    public function viewEmployees(): Action
    {
        return Action::make('viewEmployees')
            ->modalSubmitAction(false)
            ->modalCancelAction(fn ($action) => $action->label('Close'))
            ->modalWidth('md')
            ->modalHeading(fn ($arguments) => 'Employees: ' . ($arguments['category'] ?? ''))
            ->modalContent(function ($arguments) {
                $category = $arguments['category'] ?? null;

                $employees = collect();

                if ($category) {
                    foreach ($this->getSubmittedAppraisals() as $appraisal) {
                        $data = $appraisal->appraisal_form_data;

                        if (is_array($data) && ($data['job_satisfaction'] ?? '') === $category) {
                            $employees->push($appraisal->employee->name ?? 'Unknown');
                        }
                    }
                }

                return view('filament.employee.widgets.employee-list-modal', [
                    'employees' => $employees->filter()->unique()->sort()->values()
                ]);
            });
    }
}
