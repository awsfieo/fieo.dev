<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Pages;

use App\Filament\Employee\Resources\EmployeeAppraisals\EmployeeAppraisalResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeAppraisal extends EditRecord
{
    protected static string $resource = EmployeeAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
