<?php

namespace App\Jobs;

use App\Imports\RcmcRawHsCodeImport;
use App\Models\RcmcRawHsCode;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ProcessHsCodes implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $adminUserId;

    public function __construct(string $filePath, int $adminUserId)
    {
        $this->filePath = $filePath;
        $this->adminUserId = $adminUserId;
    }

    public function handle(): void
    {
        try {
            // 1. Truncate Raw Table
            RcmcRawHsCode::truncate();

            // 2. Import Excel
            Excel::import(new RcmcRawHsCodeImport, $this->filePath);

            UpdateHsCodesData::dispatch($this->adminUserId);

            Notification::make()
                ->title('HS Codes Loaded')
                ->body('Raw HS Code data successfully loaded into staging.')
                ->success()
                ->sendToDatabase(User::find($this->adminUserId));
            
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred: ' . $e->getMessage())
                ->danger()
                ->sendToDatabase(User::find($this->adminUserId));
        } finally {
            // 3. Cleanup
            Storage::disk('local')->delete($this->filePath);
        }
    }
}