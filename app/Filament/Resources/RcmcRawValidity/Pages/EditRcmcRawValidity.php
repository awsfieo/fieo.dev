<?php

namespace App\Filament\Resources\RcmcRawValidity\Pages;

use App\Filament\Resources\RcmcRawValidity\RcmcRawValidityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRcmcRawValidity extends EditRecord
{
    protected static string $resource = RcmcRawValidityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
