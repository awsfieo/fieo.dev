<?php

namespace App\Jobs;

use App\Models\RcmcRawReceipt;
use App\Models\RcmcReceipt;
use App\Models\Office;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class UpdateReceiptsData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adminUserId;
    private $failedRecords = []; // Store failures here

    public function __construct(int $adminUserId)
    {
        $this->adminUserId = $adminUserId;
    }

    public function handle(): void
    {
        $processedCount = 0;
        $failureCount = 0;

        Log::info('UpdateReceiptsData Job Started.');

        // 1. Chunk size set to 500
        RcmcRawReceipt::chunk(500, function ($rawReceipts) use (&$processedCount, &$failureCount) {
            foreach ($rawReceipts as $raw) {
                try {
                    // 2. Strict Duplicate Check (Skip if receipt_number exists)
                    if (RcmcReceipt::where('receipt_number', $raw->receipt_number)->exists()) {
                        continue; 
                    }

                    // 3. Resolve Office
                    $officeId = $this->getOfficeId($raw->office ?? '');

                    // 4. Clean "No of Years"
                    $years = (int) filter_var($raw->no_of_years, FILTER_SANITIZE_NUMBER_INT);
                    if ($years <= 0) $years = 1;

                    // 5. Extract Category
                    $category = $this->extractCategory($raw->purpose);

                    // 6. Create Record
                    RcmcReceipt::create([
                        'receipt_number' => $raw->receipt_number,
                        'receipt_date'   => $raw->receipt_date,
                        'gstin'          => $raw->gstin,
                        
                        'company_name'   => $raw->company_name,
                        'address_line_1' => $raw->address_line_1,
                        'address_line_2' => $raw->address_line_2,
                        'city'           => $raw->city,
                        'district'       => $raw->district,
                        'state'          => $raw->state,
                        'pincode'        => $raw->pincode,

                        'purpose'             => $raw->purpose,
                        'membership_category' => $category,
                        'hsn_sac_code'        => $raw->hsn_sac_code,

                        'taxable_value'       => $this->decimal($raw->taxable_value),
                        'cgst_amount'         => $this->decimal($raw->cgst_amount),
                        'sgst_amount'         => $this->decimal($raw->sgst_amount),
                        'igst_amount'         => $this->decimal($raw->igst_amount),
                        'total_receipt_value' => $this->decimal($raw->total_receipt_value),

                        'voucher_type' => $raw->voucher_type,
                        'receipt_type' => $raw->receipt_type,
                        
                        'is_tds_paid' => (strtolower($raw->is_tds_paid ?? '') === 'yes'),
                        'tds_amount'  => $this->decimal($raw->tds_amount),
                        
                        'no_of_years' => $years,

                        'current_membership_income' => $this->decimal($raw->current_membership_income),
                        
                        'fy_1' => null,
                        'fy_2' => null,
                        'fy_3' => null,
                        'fy_4' => null,
                        'fy_5' => null,

                        'advance_second_year'       => $this->decimal($raw->advance_second_year),
                        'advance_third_year'        => $this->decimal($raw->advance_third_year),
                        'advance_fourth_year'       => $this->decimal($raw->advance_fourth_year),
                        'advance_fifth_year'        => $this->decimal($raw->advance_fifth_year),

                        'office_id'  => $officeId,
                    ]);

                    $processedCount++;

                } catch (\Exception $e) {
                    // --- SAFETY CATCH: If one row fails, Log it and Continue ---
                    $failureCount++;
                    $this->failedRecords[] = [
                        'receipt' => $raw->receipt_number,
                        'error'   => $e->getMessage()
                    ];
                    
                    Log::error("Failed to process receipt: {$raw->receipt_number}. Error: " . $e->getMessage());
                }
            }
        });

        // 7. Send Final Notification with Error Details
        $this->sendFinalNotification($processedCount, $failureCount);
    }

    private function sendFinalNotification(int $success, int $failed): void
    {
        $title = 'Receipts Processing Completed';
        $body = "Successfully moved {$success} receipts to the main database.";
        $color = 'success';

        if ($failed > 0) {
            $title = 'Processing Completed with Errors';
            $body .= " <b>However, {$failed} receipts failed.</b> Check the logs for details.";
            $color = 'warning';
            
            // Log top 5 failures for quick debugging
            Log::error("--- RECEIPTS PROCESSING ERRORS ({$failed} total) ---");
            foreach (array_slice($this->failedRecords, 0, 5) as $fail) {
                Log::error("Receipt: {$fail['receipt']} | Error: {$fail['error']}");
            }
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->status($color)
            ->sendToDatabase(User::find($this->adminUserId));
    }

    // ... (Keep your helper methods extractCategory, getOfficeId, decimal exactly as they were) ...
    
    private function extractCategory(?string $purpose): ?string
    {
        if (empty($purpose)) return null;
        if (preg_match('/under\s+(.*?\bCategory)/i', $purpose, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    private function getOfficeId(string $rawOfficeName): ?int
    {
        $cleanName = trim(Str::squish($rawOfficeName));
        $upperName = Str::upper($cleanName);
        $cityPart = Str::afterLast($upperName, '-');
        $cityPart = trim($cityPart);

        if (empty($cityPart) || Str::contains($cityPart, 'CHAPTER')) {
            $searchKey = Str::remove('FIEO', $upperName);
            $searchKey = Str::remove('CHAPTER', $searchKey);
            $searchKey = Str::remove('REGION', $searchKey);
            $searchKey = Str::remove('DIVISION', $searchKey);
            $searchKey = Str::remove('-', $searchKey);
            $searchKey = trim(Str::squish($searchKey));
        } else {
            $searchKey = $cityPart;
        }

        if ($searchKey === 'NEW DELHI') return 2;
        if ($searchKey === 'RANCHI') $searchKey = 'KOLKATA';
        if ($searchKey === 'VIJAYAWADA') $searchKey = 'GUNTUR DISTRICT';

        $office = Office::where(DB::raw('UPPER(city)'), $searchKey)
            ->orWhere(DB::raw('UPPER(office)'), $searchKey)
            ->first();

        if (is_null($office)) {
            $office = Office::where(DB::raw('UPPER(city)'), 'LIKE', "%{$searchKey}%")
                ->orWhere(DB::raw('UPPER(office)'), 'LIKE', "%{$searchKey}%")
                ->first();
        }

        return $office->id ?? null;
    }

    private function decimal($val)
    {
        return $val ? (float) $val : 0.00;
    }
}