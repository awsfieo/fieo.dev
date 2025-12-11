<?php

namespace App\Filament\Employee\Resources\Appraisals\Pages;

use App\Filament\Employee\Resources\Appraisals\AppraisalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListAppraisals extends ListRecords
{
    protected static string $resource = AppraisalResource::class;

    protected function getHeaderActions(): array
    {
        // FIX: Hide "Create" button if user is DG & CEO
        if (Auth::user()->hasRole('DG & CEO')) {
            return [];
        }

        return [
            CreateAction::make(),
        ];
    }
}