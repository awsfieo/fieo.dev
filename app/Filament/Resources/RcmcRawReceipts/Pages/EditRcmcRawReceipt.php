<?php

namespace App\Filament\Resources\RcmcRawReceipts\Pages;

use App\Filament\Resources\RcmcRawReceipts\RcmcRawReceiptResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRcmcRawReceipt extends EditRecord
{
    protected static string $resource = RcmcRawReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
