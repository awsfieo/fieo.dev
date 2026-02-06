<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
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
        // 1. Force Infinite Limits
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        Log::info('UpdateReceiptsIec Job Started (CTE Mode).');

        // 2. The "Silver Bullet" Query
        // We use a CTE ('valid_directors') to create a perfect, unique list of PANs first.
        // Then we update the receipts from that clean list.
        $query = "
            WITH valid_directors AS (
                SELECT DISTINCT ON (pan) 
                    pan, 
                    iec
                FROM rcmc_directors
                WHERE iec IS NOT NULL 
                  AND LENGTH(TRIM(iec)) = 10
                  AND pan IS NOT NULL 
                  AND LENGTH(TRIM(pan)) = 10
            )
            UPDATE rcmc_receipts r
            SET iec = vd.iec
            FROM valid_directors vd
            WHERE 
                -- Target: Broken Receipts
                (r.iec IS NULL OR r.iec = '') 
                AND r.gstin IS NOT NULL 
                AND LENGTH(TRIM(r.gstin)) = 15
                
                -- Match: Extracted Receipt PAN == Director PAN
                AND vd.pan = SUBSTRING(TRIM(r.gstin), 3, 10);
        ";

        try {
            // Execute the raw SQL
            DB::statement($query);
            
            // Optional: Count how many are left (for verification)
            $remaining = DB::table('rcmc_receipts')
                ->where(function($q) { $q->whereNull('iec')->orWhere('iec', ''); })
                ->whereNotNull('gstin')
                ->count();

            Log::info("UpdateReceiptsIec Job Completed. Remaining broken rows: {$remaining}");

            if ($this->adminUserId) {
                Notification::make()
                    ->title('Bulk Update Complete')
                    ->body("Process finished. Remaining unmatched receipts: {$remaining}")
                    ->status('success')
                    ->sendToDatabase(User::find($this->adminUserId));
            }

        } catch (\Exception $e) {
            Log::error("Critical SQL Error in UpdateReceiptsIec: " . $e->getMessage());
        }
    }
}