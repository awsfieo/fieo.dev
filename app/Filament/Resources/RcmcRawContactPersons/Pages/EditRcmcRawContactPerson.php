<?php

namespace App\Filament\Resources\RcmcRawContactPersons\Pages;

use App\Filament\Resources\RcmcRawContactPersons\RcmcRawContactPersonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRcmcRawContactPerson extends EditRecord
{
    protected static string $resource = RcmcRawContactPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
