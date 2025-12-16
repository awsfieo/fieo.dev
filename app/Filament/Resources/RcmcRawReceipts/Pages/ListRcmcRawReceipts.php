<?php

namespace App\Filament\Resources\RcmcRawReceipts\Pages;

use App\Filament\Resources\RcmcRawReceipts\RcmcRawReceiptResource;
use App\Imports\RcmcRawReceiptImport;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;

class ListRcmcRawReceipts extends ListRecords
{
    protected static string $resource = RcmcRawReceiptResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // CreateAction removed to match Approved Applications
            
            Actions\Action::make('importExcel')
                ->label('Import Receipts')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->form([
                    FileUpload::make('attachment')
                        ->label('Upload Excel File')
                        ->helperText('Supported formats: .xlsx, .csv. Max size: 50MB.')
                        ->disk('local')
                        ->directory('temp-imports')
                        ->visibility('private')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 
                            'application/vnd.ms-excel', 
                            'text/csv'
                        ])
                        ->maxSize(51200) // 50MB limit
                        ->required(),
                ])
                ->action(function (array $data) {
                    $path = Storage::disk('local')->path($data['attachment']);

                    try {
                        Excel::import(new RcmcRawReceiptImport, $path);

                        Notification::make()
                            ->title('Receipts Imported Successfully')
                            ->success()
                            ->send();

                        // Cleanup temp file
                        if(file_exists($path)) {
                            unlink($path);
                        }

                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Import Failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),
        ];
    }
}