<?php

namespace App\Filament\Resources\RcmcRawApprovedApplications\Pages;

use App\Filament\Resources\RcmcRawApprovedApplications\RcmcRawApprovedApplicationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRcmcRawApprovedApplication extends EditRecord
{
    protected static string $resource = RcmcRawApprovedApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
