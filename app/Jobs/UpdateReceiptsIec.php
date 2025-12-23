<?php

namespace App\Jobs;

use App\Models\RcmcDirector;
use App\Models\RcmcReceipt;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdateReceiptsIec implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adminUserId;

    public function __construct(?int $adminUserId = null)
    {
        $this->adminUserId = $adminUserId;
    }

    public function handle(): void
    {
        Log::info('UpdateReceiptsIec Job Started.');

        $updatedCount = 0;
        $missingCount = 0;

        // 1. UPDATED QUERY: Check for NULL OR Empty String
        RcmcReceipt::query()
            ->where(function ($query) {
                $query->whereNull('iec')
                      ->orWhere('iec', '=')
                      ->orWhere('iec', '');
            })
            ->whereNotNull('gstin')
            ->where('gstin', '!=', '') // Ensure GSTIN itself isn't empty
            ->chunk(500, function ($receipts) use (&$updatedCount, &$missingCount) {
                
                foreach ($receipts as $receipt) {
                    // 2. Data Cleaning: Trim spaces from GSTIN before searching
                    $cleanGstin = trim($receipt->gstin);

                    if (empty($cleanGstin)) {
                        $missingCount++;
                        continue;
                    }

                    // 3. Search: Find matching Director
                    $director = RcmcDirector::where('gstin', $cleanGstin)
                        ->whereNotNull('iec')
                        ->where('iec', '!=', '')
                        ->select('iec')
                        ->first();

                    if ($director) {
                        // 4. Update: Fill the IEC
                        $receipt->update(['iec' => $director->iec]);
                        $updatedCount++;
                    } else {
                        $missingCount++;
                    }
                }
            });

        Log::info("UpdateReceiptsIec Job Completed. Updated: {$updatedCount}, Unmatched: {$missingCount}");

        if ($this->adminUserId) {
            Notification::make()
                ->title('Receipts Mapped to IEC')
                ->body("Success: {$updatedCount} receipts updated. {$missingCount} could not be matched.")
                ->status($missingCount > 0 ? 'warning' : 'success')
                ->sendToDatabase(User::find($this->adminUserId));
        }
    }
}