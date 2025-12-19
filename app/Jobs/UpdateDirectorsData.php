<?php

namespace App\Jobs;

use App\Models\RcmcRawDirector;
use App\Models\RcmcDirector;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class UpdateDirectorsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adminUserId;
    private $failedRecords = [];

    public function __construct(int $adminUserId)
    {
        $this->adminUserId = $adminUserId;
    }

    public function handle(): void
    {
        $processedCount = 0;
        $skippedCount = 0;
        $failureCount = 0;

        Log::info('UpdateDirectorsData Job Started.');

        RcmcRawDirector::chunk(500, function ($rawDirectors) use (&$processedCount, &$skippedCount, &$failureCount) {
            foreach ($rawDirectors as $raw) {
                try {
                    // --- 1. STRICT DUPLICATE CHECK ---
                    // Fields: iec, name, file_number, rcmc_number, rcmc_issue_date
                    if ($this->isDuplicate($raw)) {
                        $skippedCount++;
                        continue; 
                    }

                    // --- 2. PARSE DATES ---
                    $doe = $this->parseDate($raw->doe);
                    $iecIssueDate = $this->parseDate($raw->iec_issue_date);
                    $rcmcIssueDate = $this->parseDate($raw->rcmc_issue_date);
                    
                    // --- 3. INSERT NEW RECORD ---
                    RcmcDirector::create([
                        'iec'               => $raw->iec,
                        'name'              => $raw->name,
                        'pan'               => $raw->pan,
                        'company_name'      => $raw->company_name,
                        'epc_short_name'    => $raw->epc_short_name,
                        'doe'               => $doe,
                        'iec_issue_date'    => $iecIssueDate,
                        'nature_of_concern' => $raw->nature_of_concern,
                        'branch_code'       => $raw->branch_code,
                        'eou_sez'           => $raw->eou_sez,
                        'is_eou_sez'        => (int) $raw->is_eou_sez,
                        'gstin'             => $raw->gstin,
                        'all_branch'        => $raw->all_branch,
                        'file_number'       => $raw->file_number,
                        'rcmc_number'       => $raw->rcmc_number,
                        'rcmc_issue_date'   => $rcmcIssueDate,
                        
                        // Additional fields (Initialized as null)
                        'din' => null,
                        'director_pan' => null,
                        'designation' => null,
                        'email' => null,
                        'mobile' => null,
                        'phone' => null,
                    ]);

                    $processedCount++;

                } catch (\Exception $e) {
                    $failureCount++;
                    $this->failedRecords[] = [
                        'director' => $raw->name . ' (' . $raw->iec . ')',
                        'error'    => $e->getMessage()
                    ];
                    Log::error("Failed to process Director: {$raw->name} ({$raw->iec}). Error: " . $e->getMessage());
                }
            }
        });

        $this->sendFinalNotification($processedCount, $skippedCount, $failureCount);
    }

    /**
     * Checks for duplicates based on IEC, Name, File Number, RCMC Number, and RCMC Issue Date.
     */
    private function isDuplicate(RcmcRawDirector $raw): bool
    {
        $rcmcIssueDate = $this->parseDate($raw->rcmc_issue_date);

        $query = RcmcDirector::where('iec', $raw->iec)
            ->where('name', $raw->name)
            ->where('file_number', $raw->file_number)
            ->where('rcmc_number', $raw->rcmc_number);

        if ($rcmcIssueDate) {
            $query->whereDate('rcmc_issue_date', $rcmcIssueDate);
        } else {
            $query->whereNull('rcmc_issue_date');
        }

        return $query->exists();
    }

    private function parseDate($val)
    {
        return empty($val) ? null : Carbon::parse($val)->format('Y-m-d');
    }

    private function sendFinalNotification(int $success, int $skipped, int $failed): void
    {
        $title = 'Directors Processing Completed';
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