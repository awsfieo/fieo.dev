<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppraisal extends CreateRecord
{
    protected static string $resource = AppraisalResource::class;
}
