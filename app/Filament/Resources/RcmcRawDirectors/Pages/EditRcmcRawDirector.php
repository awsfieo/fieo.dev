<?php

namespace App\Filament\Resources\RcmcRawDirectors\Pages;

use App\Filament\Resources\RcmcRawDirectors\RcmcRawDirectorResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRcmcRawDirector extends EditRecord
{
    protected static string $resource = RcmcRawDirectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
