<?php

namespace App\Filament\Employee\Resources\Events\Pages;

use App\Filament\Employee\Resources\Events\EventResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEvent extends CreateRecord
{
    protected static string $resource = EventResource::class;
}
