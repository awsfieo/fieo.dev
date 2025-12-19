<?php

namespace App\Filament\Resources\RcmcRawHsCodes\Pages;

use App\Filament\Resources\RcmcRawHsCodes\RcmcRawHsCodeResource;
use App\Jobs\ProcessHsCodes;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRcmcRawHsCodes extends ListRecords
{
    protected static string $resource = RcmcRawHsCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadExcel')
                ->label('Upload HS Codes')
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

                    ProcessHsCodes::dispatch($uploadedFilePath, Auth::id()); 

                    Notification::make()
                        ->title('Processing Started')
                        ->body('HS Codes file is being processed in the background.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}