<?php

namespace App\Filament\Resources\RcmcRawHsCodes\Pages;

use App\Filament\Resources\RcmcRawHsCodes\RcmcRawHsCodeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRcmcRawHsCode extends EditRecord
{
    protected static string $resource = RcmcRawHsCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
