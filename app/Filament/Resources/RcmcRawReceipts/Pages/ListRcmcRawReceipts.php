<?php

namespace App\Filament\Resources\RcmcRawReceipts\Pages;

use App\Filament\Resources\RcmcRawReceipts\RcmcRawReceiptResource;
use App\Jobs\ProcessReceipts; // <--- The new short job name
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ListRcmcRawReceipts extends ListRecords
{
    protected static string $resource = RcmcRawReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadExcel')
                ->label('Upload Receipts')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Choose Excel File')
                        ->disk('local') 
                        ->directory('temp-uploads')
                        ->required()
                        ->storeFiles(true)
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/csv'])
                        ->maxSize(51200), // 50MB
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
                    ProcessReceipts::dispatch($uploadedFilePath, Auth::id()); 

                    Notification::make()
                        ->title('Processing Started')
                        ->body('Your file upload is running in the background. You will receive a notification upon completion.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}