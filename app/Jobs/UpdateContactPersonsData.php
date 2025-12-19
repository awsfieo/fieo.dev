<?php

namespace App\Jobs;

use App\Models\RcmcRawContactPerson;
use App\Models\RcmcContactPerson;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdateContactPersonsData implements ShouldQueue
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

        Log::info('UpdateContactPersonsData Job Started.');

        RcmcRawContactPerson::chunk(500, function ($rawRecords) use (&$processedCount, &$skippedCount, &$failureCount) {
            foreach ($rawRecords as $raw) {
                try {
                    // --- 1. STRICT DUPLICATE CHECK ---
                    // Fields: iec, name, file_number, rcmc_number, rcmc_issue_date
                    if ($this->isDuplicate($raw)) {
                        $skippedCount++;
                        continue; 
                    }

                    // --- 2. INSERT NEW RECORD ---
                    RcmcContactPerson::create([
                        'iec'             => $raw->iec,
                        'contact_type'    => $raw->contact_type,
                        
                        // New fields (Salutation/Gender/DOB are null in CSV, but exist in DB now)
                        'salutation'      => null, 
                        'name'            => $raw->name,
                        'gender'          => null,
                        'dob'             => null,
                        'designation'     => $raw->designation,
                        
                        'address_line_1'  => $raw->address_line_1,
                        'address_line_2'  => $raw->address_line_2,
                        'city'            => $raw->city,
                        'pincode'         => $raw->pincode,
                        'district'        => $raw->district,
                        'state'           => $raw->state,
                        
                        'phone'           => $raw->phone,
                        'mobile'          => $raw->mobile,
                        'email'           => $raw->email,
                        'website'         => $raw->website,
                        
                        'epc_short_name'  => $raw->epc_short_name,
                        'office'          => $raw->office,
                        'file_number'     => $raw->file_number,
                        'rcmc_number'     => $raw->rcmc_number,
                        'rcmc_issue_date' => $raw->rcmc_issue_date,
                    ]);

                    $processedCount++;

                } catch (\Exception $e) {
                    $failureCount++;
                    Log::error("Failed to process Contact Person for IEC: {$raw->iec}. Error: " . $e->getMessage());
                }
            }
        });

        $this->sendFinalNotification($processedCount, $skippedCount, $failureCount);
    }

    /**
     * Checks if a record already exists with the 5 specific matching fields.
     */
    private function isDuplicate(RcmcRawContactPerson $raw): bool
    {
        $query = RcmcContactPerson::where('iec', $raw->iec)
            ->where('name', $raw->name)
            ->where('file_number', $raw->file_number)
            ->where('rcmc_number', $raw->rcmc_number);

        if ($raw->rcmc_issue_date) {
            $query->whereDate('rcmc_issue_date', $raw->rcmc_issue_date);
        } else {
            $query->whereNull('rcmc_issue_date');
        }

        return $query->exists();
    }

    private function sendFinalNotification(int $success, int $skipped, int $failed): void
    {
        $title = 'Contact Persons Processing Completed';
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