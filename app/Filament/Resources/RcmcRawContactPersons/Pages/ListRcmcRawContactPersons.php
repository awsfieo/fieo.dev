<?php

namespace App\Filament\Resources\RcmcRawContactPersons\Pages;

use App\Filament\Resources\RcmcRawContactPersons\RcmcRawContactPersonResource;
use App\Jobs\ProcessContactPersons;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRcmcRawContactPersons extends ListRecords
{
    protected static string $resource = RcmcRawContactPersonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadExcel')
                ->label('Upload Contact Persons')
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

                    ProcessContactPersons::dispatch($uploadedFilePath, Auth::id()); 

                    Notification::make()
                        ->title('Processing Started')
                        ->body('Contact persons file is being processed in the background.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}