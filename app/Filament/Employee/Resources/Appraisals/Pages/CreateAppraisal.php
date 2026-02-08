<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Models\Appraisal;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;

class CreateAppraisal extends CreateRecord
{
    protected static string $resource = AppraisalResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        $employee = Auth::user()->employee;
        
        // Define the current period defaults (Must match your Form Schema defaults)
        $currentYear = date('Y');
        $today = Carbon::today();
       
        // --- 1. DATE VALIDATION CHECK ---
        $schedule = \App\Models\EmployeeAppraisal::query()
            ->where('employee_id', $employee?->id)
            ->where('appraisal_year', $currentYear)
            ->first();

        $isOpen = false;
        
        if ($schedule) {
             $start = $schedule->appraisal_start_date ? Carbon::parse($schedule->appraisal_start_date) : null;
             $end   = $schedule->appraisal_end_date   ? Carbon::parse($schedule->appraisal_end_date)   : null;

             // Check Standard
             if ($start && $end && $today->betweenIncluded($start, $end)) {
                 $isOpen = true;
             }
             // Check Extension
             elseif ($schedule->deadline_extension && $schedule->deadline_extension_date) {
                 if ($today->lessThanOrEqualTo($schedule->deadline_extension_date)) {
                     $isOpen = true;
                 }
             }
        }

        if (! $isOpen) {
             Notification::make()
                ->title('Appraisal Window Closed')
                ->body('The appraisal window is currently closed for you.')
                ->danger()
                ->send();
            
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }
        
        // SIMPLIFIED CHECK: 
        // Just check if ANY appraisal exists for this employee in this year.
        // We don't care if it's April or October. One per year allowed.
        $existingAppraisal = Appraisal::query()
            ->where('employee_id', $employee?->id)
            ->where('appraisal_year', $currentYear)
            ->first();

        if ($existingAppraisal) {
            // 1. Notify the user
            Notification::make()
                ->title('Appraisal Already Exists')
                ->body("You have already created an appraisal form for the year $currentYear. Please view your existing appraisal.")
                ->warning()
                ->persistent()
                ->send();

            // 2. Redirect to the existing record's View page
            $this->redirect($this->getResource()::getUrl('view', ['record' => $existingAppraisal]));
            return;
        }

        // Proceed normally if no record exists
        parent::mount();
    }
}