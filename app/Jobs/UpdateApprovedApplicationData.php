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


            // --- PASS 2: LOOKUP AND UPDATE (Decoupled Logic) ---

            $unlinkedRecords = RcmcApprovedApplication::whereNull('office_id')
                ->orWhereNull('employee_code')
                ->cursor();

            /** @var RcmcApprovedApplication $record */
            foreach ($unlinkedRecords as $record) {
                
                $updates = [];

                // --- 1. Handle Office Lookup ---
                // If the record already has an ID, use it. Otherwise, try looking it up.
                if ($record->office_id) {
                    // Already exists, no update needed for this field
                    $officeId = $record->office_id;
                } else {
                    $officeId = $this->getOfficeId($record->office);
                    
                    if ($officeId) {
                        $updates['office_id'] = $officeId;
                    } else {
                        // Log failure only if we tried to look it up and failed
                        $this->skippedLookups[] = [
                            'iec' => $record->iec,
                            'reason' => 'Office Missing', // Specific reason
                            'raw_name' => $record->closed_by,
                            'raw_office' => $record->office,
                        ];
                    }
                }

                // --- 2. Handle Employee Lookup ---
                // If the record already has a code, use it. Otherwise, try looking it up.
                if ($record->employee_code) {
                    // Already exists, no update needed for this field
                    $employeeCode = $record->employee_code;
                } else {
                    $employeeCode = $this->getEmployeeCode($record->closed_by, $record->iec);

                    if ($employeeCode) {
                        $updates['employee_code'] = $employeeCode;
                    } else {
                        // Log failure only if we tried to look it up and failed
                        $this->skippedLookups[] = [
                            'iec' => $record->iec,
                            'reason' => 'Employee Missing', // Specific reason
                            'raw_name' => $record->closed_by,
                            'raw_office' => $record->office,
                        ];
                    }
                }

                // --- 3. Perform Update if ANY data was found ---
                if (!empty($updates)) {
                    $record->update($updates);
                    $processedLookupsCount++;
                }
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
        // 1. Clean the input string
        $namePart = explode(',', $rawClosedBy)[0];
        $cleanName = trim(Str::squish($namePart));
        $upperName = Str::upper($cleanName);

        // --- Step 1: NAME EXCEPTION MAPPING ---
        // Map the "Wrong/Long Name" from Excel to the "Correct Name" in your Database.
        $nameExceptions = [
            // 'EXCEL NAME (UPPERCASE)' => 'Database Name (Exact Match)'
            'DANISHA MINU DANIEL VICTOR MERLIN' => 'Danisha Minu',
            'SENNIAPPAN SELVANAYAGI'            => 'Selvanayagi M S',
        ];

        // Check if this name is in our exception list
        if (isset($nameExceptions[$upperName])) {
            $targetDbName = $nameExceptions[$upperName];

            // Perform a specific lookup using the corrected name
            $employee = Employee::where('name', $targetDbName)->first();

            if ($employee) {
                Log::debug("Employee Lookup: Mapped exception '{$upperName}' to DB entry '{$targetDbName}'. Found Code: {$employee->employee_code}");
                return $employee->employee_code; // Returns '0293' or whatever is in the DB
            }

            Log::warning("Employee Lookup: Mapped '{$upperName}' to '{$targetDbName}', but '{$targetDbName}' does not exist in the employees table.");
            // Optional: Return null here, or let it fall through to fuzzy search
        }

        // ----------------------------------------------------------------------
        // --- Steps 2 & 3: AGGRESSIVE FUZZY LOOKUP (For all regular names) ---
        // ----------------------------------------------------------------------

        $employee = null;

        // Aggressive cleaning: Remove all whitespace and non-alphanumeric characters
        // e.g. "Danisha Minu" becomes "DANISHAMINU"
        $searchKey = preg_replace('/[^A-Z0-9]/', '', $upperName);

        // 2. PRIMARY LOOKUP: Search on 'name' column
        // Matches "Danisha Minu" in DB against search key "DANISHAMINU"
        $employee = Employee::where(DB::raw("REPLACE(UPPER(name), ' ', '')"), 'LIKE', "%{$searchKey}%")
            ->first();

        // 3. FALLBACK LOOKUP: Search on 'aadhar_name' column
        if (is_null($employee)) {
            $employee = Employee::where(DB::raw("REPLACE(UPPER(aadhar_name), ' ', '')"), 'LIKE', "%{$searchKey}%")
                ->first();
        }

        // Log failure if still null
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
