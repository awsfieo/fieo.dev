<?php

namespace App\Jobs;

use App\Models\RcmcRawApprovedApplication;
use App\Models\RcmcApprovedApplication;
use App\Models\Office;
use App\Models\Employee;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class UpdateApprovedApplicationData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $adminUserId;
    private $skippedLookups = [];

    public function __construct(int $adminUserId)
    {
        $this->adminUserId = $adminUserId;
    }

    public function handle(): void
    {
        $newRecordsCount = 0;
        $processedLookupsCount = 0;

        Log::info('UpdateApprovedApplicationData Job Started - Initiating Two-Pass System.');

        DB::transaction(function () use (&$newRecordsCount, &$processedLookupsCount) {

            // --- PASS 1: BULK COPY (Insert all non-duplicates with NULL IDs) ---
            $rawApplications = RcmcRawApprovedApplication::cursor();
            $fieldsToCopy = [
                'iec',
                'company_name',
                'file_date',
                'file_number',
                'rcmc_number',
                'application_type',
                'status',
                'closed_by',
                'office'
            ];

            // FIX: Using Model type hint in foreach for Linter compatibility
            /** @var RcmcRawApprovedApplication $rawApp */
            foreach ($rawApplications as $rawApp) {

                if ($this->isStrictDuplicate($rawApp)) {
                    if (config('app.debug')) {
                        Log::debug("Pass 1 SKIPPED: Strict Duplicate Found for IEC: {$rawApp->iec}.");
                    }
                    continue;
                }

                // Prepare attributes for insertion
                $attributes = array_intersect_key($rawApp->toArray(), array_flip($fieldsToCopy));

                RcmcApprovedApplication::create(array_merge($attributes, [
                    'employee_code' => null,
                    'office_id'     => null,
                ]));

                $newRecordsCount++;
            }

            Log::info("Pass 1 Complete: {$newRecordsCount} non-duplicate records copied to master table with NULL keys.");


            // --- PASS 2: LOOKUP AND UPDATE (Only process newly inserted records with missing IDs) ---

            $unlinkedRecords = RcmcApprovedApplication::whereNull('office_id')
                ->orWhereNull('employee_code')
                ->cursor();

            // FIX: Using Model type hint in foreach for Linter compatibility
            /** @var RcmcApprovedApplication $record */
            foreach ($unlinkedRecords as $record) {

                $officeId = $record->office_id ?? $this->getOfficeId($record->office);
                $employeeCode = $record->employee_code ?? $this->getEmployeeCode($record->closed_by, $record->iec);

                if (is_null($officeId) || is_null($employeeCode)) {

                    $this->skippedLookups[] = [
                        'iec' => $record->iec,
                        'reason' => is_null($officeId) ? 'Office Missing' : 'Employee Missing',
                        'raw_name' => $record->closed_by,
                        'raw_office' => $record->office,
                    ];

                    continue;
                }

                $record->update([
                    'office_id' => $officeId,
                    'employee_code' => $employeeCode,
                ]);

                $processedLookupsCount++;
            }
        });

        // 5. Send Final Notification
        $this->sendFinalNotification($newRecordsCount, $processedLookupsCount);
    }

    /**
     * Strict check: returns true only if an existing record matches ALL data fields exactly.
     */
    private function isStrictDuplicate(RcmcRawApprovedApplication $rawApp): bool
    {
        $fields = ['iec', 'company_name', 'file_date', 'file_number', 'rcmc_number', 'application_type', 'status', 'closed_by', 'office'];
        $query = RcmcApprovedApplication::query();
        foreach ($fields as $field) {
            $rawValue = trim((string)$rawApp->{$field});
            $query->where($field, $rawValue);
        }
        return $query->exists();
    }


    /**
     * Office Lookup Logic: New Delhi is hardcoded to ID=2.
     */
    // In app/Jobs/UpdateApprovedApplicationData.php

    private function getOfficeId(string $rawOfficeName): ?int
    {
        $cleanName = trim(Str::squish($rawOfficeName));
        $upperName = Str::upper($cleanName);

        // --- Step 1: AGGRESSIVE CLEANUP & CITY ISOLATION ---
        // Extract the primary city name for both lookups and specific logic
        $cityPart = Str::afterLast($upperName, '-');
        $cityPart = trim($cityPart);

        // Fallback if no hyphen is present (e.g., "FIEO Coimbatore Chapter")
        if (empty($cityPart) || Str::contains($cityPart, 'CHAPTER')) {
            // Aggressively clean the entire raw name to isolate the region/city
            $searchKey = Str::remove('FIEO', $upperName);
            $searchKey = Str::remove('CHAPTER', $searchKey);
            $searchKey = Str::remove('REGION', $searchKey);
            $searchKey = Str::remove('DIVISION', $searchKey);
            $searchKey = Str::remove('-', $searchKey);
            $searchKey = trim(Str::squish($searchKey));
        } else {
            $searchKey = $cityPart;
        }


        // --- Step 2: HARDCODED EXCEPTIONS (Your Business Rules) ---

        // REQUIRED FIX 1: New Delhi -> Northern Region (ID=2)
        if ($searchKey === 'NEW DELHI') {
            return 2;
        }
        // REQUIRED FIX 2: Ranchi must match Kolkata
        if ($searchKey === 'RANCHI') {
            $searchKey = 'KOLKATA';
        }
        // REQUIRED FIX 3: Vijayawada must map to Guntur District
        if ($searchKey === 'VIJAYAWADA') {
            $searchKey = 'GUNTUR DISTRICT';
        }


        // --- Step 3: DATABASE LOOKUP (Final Attempt for all cities) ---
        // Searches the 'city' column first, then falls back to the 'office' column
        $office = Office::where(DB::raw('UPPER(city)'), $searchKey)
            ->orWhere(DB::raw('UPPER(office)'), $searchKey)
            ->first();

        // Fallback: If no exact match found, try a fuzzy match on the key
        if (is_null($office)) {
            $office = Office::where(DB::raw('UPPER(city)'), 'LIKE', "%{$searchKey}%")
                ->orWhere(DB::raw('UPPER(office)'), 'LIKE', "%{$searchKey}%")
                ->first();
        }

        return $office->id ?? null;
    }

    /**
     * Employee Lookup Logic: Primary name, then Aadhar name fallback (with aggressive cleaning).
     */
    // In app/Jobs/UpdateApprovedApplicationData.php -> getEmployeeCode method

    private function getEmployeeCode(string $rawClosedBy, string $iec): ?string
    {
        $namePart = explode(',', $rawClosedBy)[0];
        $cleanName = trim(Str::squish($namePart));
        $upperName = Str::upper($cleanName);

        $employee = null;

        // --- Step 1: HARDCODED MAPPING (The final rule to ensure zero skips for known names) ---
        // NOTE: The value in the array (e.g., 'DanishCode') should be the actual employee_code
        // for the employee in your master 'employees' table.
        $hardcodedMapping = [
            // Raw Name from Excel -> Cleaned Name from Lookup (e.g., Danisha Minu D M -> DANISHA MINU D M)
            'DANISHA MINU DANIEL VICTOR MERLIN' => 'Danisha Minu Employee Code', // Replace with Danisha Minu's actual code

            // Raw Name from Excel -> Cleaned Name from Lookup (e.g., S SELVANAYAGI VIJAYKUMAR -> S SELVANAYAGI VIJAYKUMAR)
            'SENNIAPPAN SELVANAYAGI' => 'Selvanayagi M S Employee Code', // Replace with Selvanayagi M S's actual code
        ];

        if (isset($hardcodedMapping[$upperName])) {
            // We found a direct, hardcoded match for a known problematic name.
            Log::debug("Employee Lookup: Found HARDCODED exception for {$upperName}. Code={$hardcodedMapping[$upperName]}");
            return $hardcodedMapping[$upperName];
        }

        // ----------------------------------------------------------------------
        // --- Steps 2 & 3: AGGRESSIVE FUZZY LOOKUP (For all other non-hardcoded names) ---
        // ----------------------------------------------------------------------

        // Aggressive cleaning: Remove all whitespace and non-alphanumeric characters from the Excel input name
        $searchKey = preg_replace('/[^A-Z0-9]/', '', $upperName);

        // 2. PRIMARY LOOKUP: Search on 'name' column (Cleaned)
        $employee = Employee::where(DB::raw("REPLACE(UPPER(name), ' ', '')"), 'LIKE', "%{$searchKey}%")
            ->first();

        // 3. FALLBACK LOOKUP: Search on 'aadhar_name' column (Cleaned)
        if (is_null($employee)) {
            $employee = Employee::where(DB::raw("REPLACE(UPPER(aadhar_name), ' ', '')"), 'LIKE', "%{$searchKey}%")
                ->first();
        }

        // If the aggressive lookup failed for non-hardcoded names, log the failure.
        if (is_null($employee)) {
            Log::error("FINAL LOOKUP FAILURE: No match found for raw name '{$rawClosedBy}' (Search Key: {$searchKey}). IEC: {$iec}.");
        }

        return $employee->employee_code ?? null;
    }

    /**
     * Sends the final Filament notification detailing the outcome.
     */
    private function sendFinalNotification(int $insertedCount, int $processedCount): void
    {
        $totalFailedLookups = count($this->skippedLookups);
        $title = 'Data Update Complete';
        $body = "Pass 1 inserted {$insertedCount} new records. Pass 2 linked {$processedCount} records with IDs.";
        $color = 'success';

        if ($totalFailedLookups > 0) {
            $title = 'Data Update Complete with Errors';
            $failedDetails = array_slice($this->skippedLookups, 0, 5);
            $detailList = '';
            foreach ($failedDetails as $failure) {
                $detailList .= "<li>IEC: {$failure['iec']} - Reason: {$failure['reason']} (Raw Name: {$failure['raw_name']})</li>";
            }

            $body .= " **WARNING:** {$totalFailedLookups} records could not be linked (missing IDs). Please check logs for full details. <br> **First 5 errors:** <ul>{$detailList}</ul>";
            $color = 'danger';

            Log::error('--- LOOKUP ERRORS REQUIRING MANUAL REVIEW ---');
            foreach ($this->skippedLookups as $failure) {
                Log::error("Manual Review: IEC: {$failure['iec']}, Reason: {$failure['reason']}, Raw Name: {$failure['raw_name']}, Raw Office: {$failure['raw_office']}");
            }
        }

        Notification::make()
            ->title($title)
            ->body($body)
            ->{$color}()
            ->sendToDatabase(\App\Models\User::find($this->adminUserId));
    }
}
