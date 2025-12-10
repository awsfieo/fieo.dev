<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAppraisal extends EditRecord
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        // Redirect to the View page after saving so they see the "Submit" button
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}