<?php

namespace App\Filament\Resources\RcmcRawDirectors\Pages;

use App\Filament\Resources\RcmcRawDirectors\RcmcRawDirectorResource;
use App\Jobs\ProcessDirectors;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListRcmcRawDirectors extends ListRecords
{
    protected static string $resource = RcmcRawDirectorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('uploadExcel')
                ->label('Upload Directors Data')
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

                    // Dispatch the Job
                    ProcessDirectors::dispatch($uploadedFilePath, Auth::id()); 

                    Notification::make()
                        ->title('Processing Started')
                        ->body('Directors file is being processed in the background.')
                        ->warning()
                        ->send();
                }),
        ];
    }
}