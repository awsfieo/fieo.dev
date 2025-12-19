<?php

namespace App\Jobs;

use App\Imports\RcmcRawContactPersonImport;
use App\Models\RcmcRawContactPerson;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;

class ProcessContactPersons implements ShouldQueue
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
            RcmcRawContactPerson::truncate();

            Excel::import(new RcmcRawContactPersonImport, $this->filePath);

            // --- ADD THIS LINE ---
            UpdateContactPersonsData::dispatch($this->adminUserId); 

            Notification::make()
                ->title('Contact Persons Loaded')
                ->body('Raw data loaded. Processing final records...')
                ->success()
                ->sendToDatabase(User::find($this->adminUserId));
            
        } catch (\Throwable $e) {
            // (Keep existing error handling)
            Notification::make()
                ->title('Import Failed')
                ->body('An error occurred: ' . $e->getMessage())
                ->danger()
                ->sendToDatabase(User::find($this->adminUserId));
        } finally {
            Storage::disk('local')->delete($this->filePath);
        }
    }
}