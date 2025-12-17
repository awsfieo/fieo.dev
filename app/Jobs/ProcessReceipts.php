<?php

namespace App\Jobs;

use App\Imports\RcmcRawReceiptImport;
use App\Models\RcmcRawReceipt;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Jobs\UpdateReceiptsData;

class ProcessReceipts implements ShouldQueue
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
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // 1. Wipe the raw table clean
            RcmcRawReceipt::truncate();

            // 2. Import the new file
            Excel::import(new RcmcRawReceiptImport, $this->filePath);

            // 3. Dispatch the final processing job (We will create this later)
            UpdateReceiptsData::dispatch($this->adminUserId); 

            // 4. Send success notification
            Notification::make()
                ->title('Receipts File Loaded')
                ->body('Raw receipts successfully loaded. Data is ready for processing.')
                ->success()
                ->sendToDatabase(User::find($this->adminUserId));
            
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred during receipts import: ' . $e->getMessage())
                ->danger()
                ->sendToDatabase(User::find($this->adminUserId));
        } finally {
            // 5. Clean up the file
            Storage::disk('local')->delete($this->filePath);
        }
    }
}