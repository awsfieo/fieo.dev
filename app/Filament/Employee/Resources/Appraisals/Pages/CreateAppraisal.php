<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Models\Appraisal;
use App\Models\EmployeeAppraisal;
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
        $employee = \Illuminate\Support\Facades\Auth::user()->employee;
        $currentYear = date('Y');
       
        // 1. Get Schedule
        $schedule = EmployeeAppraisal::query()
            ->where('employee_id', $employee?->id)
            ->where('appraisal_year', $currentYear)
            ->first();

        // 2. USE CENTRALIZED LOGIC
        // If no schedule exists OR the window is closed -> Kick them out
        if (! $schedule || ! $schedule->isSubmissionWindowOpen()) {
             Notification::make()
                ->title('Appraisal Window Closed')
                ->body('The appraisal window is currently closed for you.')
                ->danger()
                ->send();
            
            $this->redirect($this->getResource()::getUrl('index'));
            return;
        }
        
        // 3. Check for duplicates (Existing Logic)
        $existingAppraisal = \App\Models\Appraisal::query()
            ->where('employee_id', $employee?->id)
            ->where('appraisal_year', $currentYear)
            ->first();

        if ($existingAppraisal) {
            Notification::make()
                ->title('Appraisal Already Exists')
                ->body("You have already created an appraisal form for the year $currentYear.")
                ->warning()
                ->persistent()
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $existingAppraisal]));
            return;
        }

        parent::mount();
    }
}