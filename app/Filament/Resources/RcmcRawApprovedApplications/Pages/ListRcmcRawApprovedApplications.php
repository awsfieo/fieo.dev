<?php

// A. This namespace must match the folder path exactly.
namespace App\Filament\Resources\RcmcRawApprovedApplications\Pages;

use App\Filament\Resources\RcmcRawApprovedApplications\RcmcRawApprovedApplicationResource;
use App\Imports\RcmcRawApprovedApplicationImport;
use App\Jobs\ProcessApprovedApplications;
use App\Models\RcmcRawApprovedApplication;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class ListRcmcRawApprovedApplications extends ListRecords
{
    // B. This must be the static property declaration that the parent Page class is looking for.
    protected static string $resource = RcmcRawApprovedApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadExcel')
                ->label('Upload Approved Applications')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Choose Excel File')
                        ->disk('local') 
                        ->directory('temp-uploads')
                        ->required()
                        ->storeFiles(true)
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv']),
                ])
                ->action(function (array $data) {
                    $uploadedFilePath = $data['attachment'];

                    if (empty($uploadedFilePath)) {
                        Notification::make()
                            ->title('Upload Failed')
                            ->body('No file was found in the attachment field.')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Dispatch the job immediately
                    ProcessApprovedApplications::dispatch($uploadedFilePath, Auth::id()); 

                    Notification::make()
                        ->title('Processing Started')
                        ->body('Your file upload is running in the background. You will receive a notification upon completion.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}