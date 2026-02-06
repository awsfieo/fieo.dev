<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Pages;

use App\Filament\Employee\Resources\EmployeeAppraisals\EmployeeAppraisalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployeeAppraisal extends CreateRecord
{
    protected static string $resource = EmployeeAppraisalResource::class;
}
