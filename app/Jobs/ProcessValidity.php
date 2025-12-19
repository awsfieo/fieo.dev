<?php

namespace App\Jobs;

use App\Imports\RcmcRawValidityImport;
use App\Models\RcmcRawValidity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ProcessValidity implements ShouldQueue
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
            RcmcRawValidity::truncate();

            Excel::import(new RcmcRawValidityImport, $this->filePath);

            // --- ADD THIS LINE ---
            UpdateValidityData::dispatch($this->adminUserId); 

            Notification::make()
                ->title('Validity Data Loaded')
                ->body('Raw data loaded. Processing final records...')
                ->success()
                ->sendToDatabase(User::find($this->adminUserId));
            
        } catch (\Throwable $e) {
            // ... (keep existing error handling)
        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }
}