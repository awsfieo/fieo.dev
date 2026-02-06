<?php

namespace App\Filament\Employee\Resources\EmployeeAppraisals\Pages;

use App\Filament\Employee\Resources\EmployeeAppraisals\EmployeeAppraisalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeAppraisals extends ListRecords
{
    protected static string $resource = EmployeeAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
