<?php

namespace App\Jobs;

use App\Imports\RcmcRawDirectorImport;
use App\Models\RcmcRawDirector;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use App\Jobs\UpdateDirectorsData;

class ProcessDirectors implements ShouldQueue
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
            RcmcRawDirector::truncate();

            // 2. Import Excel to Raw Table
            Excel::import(new RcmcRawDirectorImport, $this->filePath);

            // 3. Dispatch the Update Job
            UpdateDirectorsData::dispatch($this->adminUserId); 

            // 4. Notify Success
            Notification::make()
                ->title('Directors File Loaded')
                ->body('Raw directors successfully loaded. Processing data...')
                ->success()
                ->sendToDatabase(User::find($this->adminUserId));
            
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred: ' . $e->getMessage())
                ->danger()
                ->sendToDatabase(User::find($this->adminUserId));
        } finally {
            // 5. Cleanup File
            Storage::disk('local')->delete($this->filePath);
        }
    }
}