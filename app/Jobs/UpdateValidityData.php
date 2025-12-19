<?php

namespace App\Jobs;

use App\Models\RcmcRawValidity;
use App\Models\RcmcValidity;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdateValidityData implements ShouldQueue
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

        Log::info('UpdateValidityData Job Started.');

        RcmcRawValidity::chunk(500, function ($rawRecords) use (&$processedCount, &$skippedCount, &$failureCount) {
            foreach ($rawRecords as $raw) {
                try {
                    // --- 1. STRICT DUPLICATE CHECK ---
                    // Fields: iec, file_number, rcmc_number, rcmc_issue_date, rcmc_valid_upto
                    if ($this->isDuplicate($raw)) {
                        $skippedCount++;
                        continue; 
                    }

                    // --- 2. PREPARE NUMBERS ---
                    // Remove commas if present in raw string
                    $annualTurnover = $this->cleanNumber($raw->annual_turnover);
                    $exportTurnover = $this->cleanNumber($raw->export_turnover);
                    $exportPerformance = $this->cleanNumber($raw->export_performance);

                    // --- 3. INSERT NEW RECORD ---
                    RcmcValidity::create([
                        'iec'                => $raw->iec,
                        'file_number'        => $raw->file_number,
                        'rcmc_number'        => $raw->rcmc_number,
                        'rcmc_issue_date'    => $raw->rcmc_issue_date,
                        'rcmc_valid_upto'    => $raw->rcmc_valid_upto,
                        
                        'epc_short_name'     => $raw->epc_short_name,
                        'application_status' => $raw->application_status,
                        'msme_status'        => $raw->msme_status,
                        'star_rating'        => $raw->star_rating,
                        
                        'annual_turnover'    => $annualTurnover,
                        'export_turnover'    => $exportTurnover,
                        'export_performance' => $exportPerformance,
                    ]);

                    $processedCount++;

                } catch (\Exception $e) {
                    $failureCount++;
                    Log::error("Failed to process Validity Data for IEC: {$raw->iec}. Error: " . $e->getMessage());
                }
            }
        });

        $this->sendFinalNotification($processedCount, $skippedCount, $failureCount);
    }

    /**
     * Checks if a record already exists with the 5 specific matching fields.
     */
    private function isDuplicate(RcmcRawValidity $raw): bool
    {
        $query = RcmcValidity::where('iec', $raw->iec)
            ->where('file_number', $raw->file_number)
            ->where('rcmc_number', $raw->rcmc_number);

        // Date Checks (Handle potential nulls from Raw Data)
        if ($raw->rcmc_issue_date) {
            $query->whereDate('rcmc_issue_date', $raw->rcmc_issue_date);
        } else {
            $query->whereNull('rcmc_issue_date');
        }

        if ($raw->rcmc_valid_upto) {
            $query->whereDate('rcmc_valid_upto', $raw->rcmc_valid_upto);
        } else {
            $query->whereNull('rcmc_valid_upto');
        }

        return $query->exists();
    }

    private function cleanNumber($val)
    {
        if (empty($val)) return 0;
        return (float) str_replace(',', '', (string)$val);
    }

    private function sendFinalNotification(int $success, int $skipped, int $failed): void
    {
        $title = 'Validity Data Processing Completed';
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