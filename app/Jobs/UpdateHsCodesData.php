<?php

namespace App\Jobs;

use App\Models\RcmcRawHsCode;
use App\Models\RcmcHsCode;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdateHsCodesData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adminUserId;

    public function __construct(int $adminUserId)
    {
        $this->adminUserId = $adminUserId;
    }

    public function handle(): void
    {
        $processedCount = 0;
        $skippedCount = 0;
        $failureCount = 0;

        Log::info('UpdateHsCodesData Job Started.');

        RcmcRawHsCode::chunk(500, function ($rawRecords) use (&$processedCount, &$skippedCount, &$failureCount) {
            foreach ($rawRecords as $raw) {
                try {
                    // --- 1. STRICT 4-FIELD DUPLICATE CHECK ---
                    // Fields: iec, hs_code, rcmc_issue_date, file_number
                    if ($this->isDuplicate($raw)) {
                        $skippedCount++;
                        continue; 
                    }

                    // --- 2. PREPARE DERIVED DATA ---
                    // Extract Chapter (First 2 digits)
                    $hsCode = (string) $raw->hs_code;
                    $chapter = strlen($hsCode) >= 2 ? substr($hsCode, 0, 2) : null;

                    // --- 3. INSERT NEW RECORD ---
                    RcmcHsCode::create([
                        'iec'                 => $raw->iec,
                        'hs_code'             => $hsCode,
                        'hs_chapter'          => $chapter,
                        
                        'company_name'        => $raw->company_name,
                        'epc_short_name'      => $raw->epc_short_name,
                        'export_type'         => $raw->export_type,
                        
                        'product_description' => $raw->product_description,
                        'business_line'       => $raw->business_line,
                        'general_description' => $raw->general_description,
                        
                        'product_images'      => [], // Initialize empty JSON array
                        
                        'file_number'         => $raw->file_number,
                        'rcmc_number'         => $raw->rcmc_number,
                        'rcmc_issue_date'     => $raw->rcmc_issue_date,
                    ]);

                    $processedCount++;

                } catch (\Exception $e) {
                    $failureCount++;
                    Log::error("Failed to process HS Code for IEC: {$raw->iec}. Error: " . $e->getMessage());
                }
            }
        });

        $this->sendFinalNotification($processedCount, $skippedCount, $failureCount);
    }

    /**
     * Checks if a record already exists with the 4 specific matching fields.
     */
    private function isDuplicate(RcmcRawHsCode $raw): bool
    {
        $query = RcmcHsCode::where('iec', $raw->iec)
            ->where('hs_code', $raw->hs_code)
            ->where('file_number', $raw->file_number);

        if ($raw->rcmc_issue_date) {
            $query->whereDate('rcmc_issue_date', $raw->rcmc_issue_date);
        } else {
            $query->whereNull('rcmc_issue_date');
        }

        return $query->exists();
    }

    private function sendFinalNotification(int $success, int $skipped, int $failed): void
    {
        $title = 'HS Codes Processing Completed';
        $body = "Inserted: {$success} | Skipped: {$skipped}";
        $color = 'success';

        if ($failed > 0) {
            $title = 'Processing Completed with Conflicts';
            $body .= " | <b>Failed: {$failed}</b>. Check logs.";
            $color = 'warning';
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->status($color)
            ->sendToDatabase(User::find($this->adminUserId));
    }
}