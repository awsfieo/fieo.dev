<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use App\Models\Appraisal;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class CreateAppraisal extends CreateRecord
{
    protected static string $resource = AppraisalResource::class;

    protected static bool $canCreateAnother = false;

    public function mount(): void
    {
        $employee = Auth::user()->employee;
        
        // Define the current period defaults (Must match your Form Schema defaults)
        $currentYear = date('Y');
        $currentCycle = 'April'; 

        // Check if a record already exists
        $existingAppraisal = Appraisal::query()
            ->where('employee_id', $employee?->id)
            ->where('appraisal_year', $currentYear)
            ->where('appraisal_cycle', $currentCycle)
            ->first();

        if ($existingAppraisal) {
            // 1. Notify the user
            Notification::make()
                ->title('Appraisal Already Exists')
                ->body("You have already created an appraisal form for the year $currentYear. Please view or edit your existing appraisal.")
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