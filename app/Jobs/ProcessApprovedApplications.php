<?php

namespace App\Jobs;

use App\Imports\RcmcRawApprovedApplicationImport;
use App\Models\RcmcRawApprovedApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Jobs\UpdateApprovedApplicationData; // <-- The correct next job name

class ProcessApprovedApplications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $adminUserId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath, int $adminUserId)
    {
        $this->filePath = $filePath;
        $this->adminUserId = $adminUserId;
    }

    /**
     * Execute the job. (This is the single, correct handle method)
     */
    public function handle(): void
    {
        try {
            // 1. Wipe the raw table clean
            RcmcRawApprovedApplication::truncate();

            // 2. Import the new file from the stable path (Chunk reading handles memory)
            Excel::import(new RcmcRawApprovedApplicationImport, $this->filePath);

            // 3. Dispatch the final processing job
            UpdateApprovedApplicationData::dispatch($this->adminUserId); 

            // 4. Send success notification for the import step
            Notification::make()
                ->title('Approved Applications File Loaded')
                ->body('Raw file successfully loaded. Starting data processing now in the background.')
                ->success()
                ->sendToDatabase(\App\Models\User::find($this->adminUserId));
            
        } catch (\Throwable $e) {
            // Send failure notification with error details
            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred during raw file import: ' . $e->getMessage())
                ->danger()
                ->sendToDatabase(\App\Models\User::find($this->adminUserId));
        } finally {
            // 5. Clean up the file from the disk after processing
            // Use Storage::disk('local')->delete() as $this->filePath is the relative path from the FileUpload
            Storage::disk('local')->delete($this->filePath);
        }
    }
}