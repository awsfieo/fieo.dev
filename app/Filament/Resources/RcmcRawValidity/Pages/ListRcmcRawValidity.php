<?php

namespace App\Filament\Resources\RcmcRawValidity\Pages;

use App\Filament\Resources\RcmcRawValidity\RcmcRawValidityResource;
use App\Jobs\ProcessValidity;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRcmcRawValidity extends ListRecords
{
    protected static string $resource = RcmcRawValidityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadExcel')
                ->label('Upload Validity Data')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Choose Excel File')
                        ->disk('local') 
                        ->directory('temp-uploads')
                        ->required()
                        ->storeFiles(true)
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                            'text/csv'
                        ])
                        ->maxSize(51200),
                ])
                ->action(function (array $data) {
                    $uploadedFilePath = $data['attachment'];

                    if (empty($uploadedFilePath)) {
                        Notification::make()
                            ->title('Upload Failed')
                            ->body('No file was found.')
                            ->danger()
                            ->send();
                        return;
                    }

                    ProcessValidity::dispatch($uploadedFilePath, Auth::id()); 

                    Notification::make()
                        ->title('Processing Started')
                        ->body('Validity data is being processed in the background.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}