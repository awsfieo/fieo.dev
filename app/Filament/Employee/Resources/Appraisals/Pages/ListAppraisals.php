<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Models\Appraisal;
use App\Models\EmployeeAppraisal;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class ListAppraisals extends ListRecords
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        // 1. Hide for DG & CEO (as before)
        if (Auth::user()->hasRole('DG & CEO')) {
            return [];
        }

        return [
            CreateAction::make()
                ->label('New Appraisal')
                // LOGIC TO DISABLE/HIDE BUTTON BASED ON DATES
                ->visible(function () {
                    $user = Auth::user();
                    $employee = $user->employee;
                    if (! $employee) return false;

                    $currentYear = date('Y');
                    $today = Carbon::today();

                    // --- 1. CHECK IF ALREADY CREATED ---
                    // If an appraisal exists for this year, hide the button immediately.
                    $alreadyExists = Appraisal::query()
                        ->where('employee_id', $employee->id)
                        ->where('appraisal_year', $currentYear)
                        ->exists();

                    if ($alreadyExists) {
                        return false; 
                    }

                    // 2. Find the Schedule from EmployeeAppraisals table
                    $schedule = EmployeeAppraisal::query()
                        ->where('employee_id', $employee->id)
                        ->where('appraisal_year', $currentYear)
                        ->first();

                    if (! $schedule) {
                        return false; // No schedule assigned by HR yet
                    }

                    // 3. Check Standard Window
                    $start = $schedule->appraisal_start_date ? Carbon::parse($schedule->appraisal_start_date) : null;
                    $end   = $schedule->appraisal_end_date   ? Carbon::parse($schedule->appraisal_end_date)   : null;
                    
                    if ($start && $end && $today->betweenIncluded($start, $end)) {
                        return true;
                    }

                    // 4. Check Extension Window
                    if ($schedule->deadline_extension && $schedule->deadline_extension_date) {
                        $extDate = Carbon::parse($schedule->deadline_extension_date);
                        if ($today->lessThanOrEqualTo($extDate)) {
                            return true;
                        }
                    }

                    return false; // Neither window is open
                })
                // OPTIONAL: Add a tooltip or disabled state instead of hiding completely?
                // If you prefer to SHOW the button but block click with a message, use ->disabled() logic instead.
                // For now, ->visible() is cleaner as it hides the button when not allowed.
        ];
    }
}