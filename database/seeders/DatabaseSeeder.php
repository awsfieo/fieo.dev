<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Clear Spatie cached permissions/roles and ensure base roles exist
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Base Roles
        Role::firstOrCreate(['name' => 'Super Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Employee',    'guard_name' => 'web']);

        // 2. Workflow Roles (Tour Claims)

        $workflowRoles = [
            // 1. Top Management (Keep specific, easier to manage)
            'DG & CEO',
            'HOD Finance', // Or just 'HOD' if they are in the 'Finance' Dept
            'Accounts Executive HO', // Specific: The central gatekeeper for all regions

            // 2. The Generic "Worker" Roles (The system matches these by Department/Region)
            'HOD',                  // Matches any Department Head
            'Chapter Head',         // Matches any Chapter Head
            'Regional Head',        // Matches any Regional Head (NR, ER, WR, SR)
            'Accounts Executive',   // Matches any Accounts person (HO, NR, etc.)
        ];

        foreach ($workflowRoles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // Seeders in order
        $this->seedUsers();
        $this->seedOffices();
        $this->seedDesignations();
        $this->seedDepartments();
        $this->seedEmployees();
        $this->seedCurrentAllocations();
    }

    private function seedUsers(): void
    {
        $path = database_path('seeders/data/users.csv');
        if (! file_exists($path)) {
            $this->command?->error("CSV file not found at: $path");
            return;
        }

        $rows = array_map('str_getcsv', file($path));
        $created = 0;
        $updated = 0;
        $assigned = 0;

        foreach ($rows as $row) {
            if (count($row) < 3) continue;

            $name     = trim($row[0]);
            $email    = strtolower(trim($row[1]));
            $password = trim($row[2]);

            // skip header or invalid emails
            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) continue;

            $exists = DB::table('users')->where('email', $email)->exists();

            DB::table('users')->updateOrInsert(
                ['email' => $email],
                [
                    'name'              => $name,
                    'email'             => $email,
                    'password'          => Hash::make($password ?: 'password'),
                    'email_verified_at' => now(),
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ]
            );

            $exists ? $updated++ : $created++;

            if ($user = User::where('email', $email)->first()) {
                $isEmployee = str_ends_with($email, '@fieo.org');
                $isVerified = ! is_null($user->email_verified_at);

                if ($isEmployee && $isVerified && ! $user->hasRole('Employee')) {
                    $user->assignRole('Employee');
                    $assigned++;
                }

                if ($email === 'admin@fieo.org' && ! $user->hasRole('Super Admin')) {
                    $user->assignRole('Super Admin');
                }

                // --- PASTE THIS AFTER THE SUPER ADMIN CHECK ---

                // 2. DG & CEO
                if ($email === 'ajaysahai@fieo.org' && ! $user->hasRole('DG & CEO')) {
                    $user->assignRole('DG & CEO');
                }

                // 3. HOD Finance
                if ($email === 'sushilkumar@fieo.org' && ! $user->hasRole('HOD Finance')) {
                    $user->assignRole('HOD Finance');
                }

                // 4. Accounts Executive HO
                if ($email === 'rakeshchand@fieo.org' && ! $user->hasRole('Accounts Executive HO')) {
                    $user->assignRole('Accounts Executive HO');
                }

                // 5. Regional Head
                $regionalHeads = [
                    'ashishjain@fieo.org',
                    'juinchoudhury@fieo.org',
                    'rktiwari@fieo.org',
                    'unni@fieo.org',
                ];
                if (in_array($email, $regionalHeads) && ! $user->hasRole('Regional Head')) {
                    $user->assignRole('Regional Head');
                }

                // 6. Chapter Head
                $chapterHeads = [
                    'aloksrivastava@fieo.org',
                    'amitkumarbaretha@fieo.org',
                    'bhupindersingh@fieo.org',
                    'jpgoel@fieo.org',
                    'kksahoo@fieo.org',
                    'kaushikdutta@fieo.org',
                    'babu@fieo.org',
                    'rajeev@fieo.org',
                    'kulkarni@fieo.org',
                    'soma@fieo.org',
                    'swaminathan@fieo.org',
                    'vinaysharma@fieo.org'
                ];
                if (in_array($email, $chapterHeads) && ! $user->hasRole('Chapter Head')) {
                    $user->assignRole('Chapter Head');
                }

                // 7. Accounts Executive (Regional)
                $accountsExecutives = [
                    'grprajapati@fieo.org',
                    'jayeetaroy@fieo.org',
                    'snazurudeen@fieo.org',
                    'ritahans@fieo.org'
                ];
                if (in_array($email, $accountsExecutives) && ! $user->hasRole('Accounts Executive')) {
                    $user->assignRole('Accounts Executive');
                }

                // 8. HOD
                $hods = [
                    'apsrivastava@fieo.org',
                    'dhananjay@fieo.org',
                    'nirmalatete@fieo.org',
                    'niteshmishra@fieo.org',
                    'ptsrinath@fieo.org',
                    'prashantseth@fieo.org',
                    'pratiknavale@fieo.org',
                    'suvidhshah@fieo.org'
                ];
                if (in_array($email, $hods) && ! $user->hasRole('HOD')) {
                    $user->assignRole('HOD');
                }
            }
        }

        $this->command?->info("Users Created: $created, Updated: $updated, Assigned 'Employee' Role: $assigned.");
    }

    private function seedOffices(): void
    {
        $csv = database_path('seeders/data/offices.csv'); // headers (any order):
        // office,address,city,state,pin,email,phone,fax,country,latitude,longitude,sort_id,parent_id,is_active,type

        if (! file_exists($csv)) {
            $this->command?->warn("Offices CSV not found at: $csv");
            return;
        }

        if (! \Illuminate\Support\Facades\Schema::hasTable('offices')) {
            $this->command?->warn("Table 'offices' not found. Run the migration, then re-run seeder.");
            return;
        }

        $lines = file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            $this->command?->warn('Offices CSV is empty.');
            return;
        }

        // parse (comma first, fallback to semicolon)
        $rows = array_map(fn($line) => str_getcsv($line, ','), $lines);
        if (count($rows) && count($rows[0]) === 1 && str_contains((string) $rows[0][0], ';')) {
            $rows = array_map(fn($line) => str_getcsv($line, ';'), $lines);
        }

        // normalize headers (lowercase, trim, strip BOM on first cell)
        $normalize = function (string $v, bool $stripBom = false): string {
            if ($stripBom) {
                $v = preg_replace('/^\xEF\xBB\xBF/', '', $v);
            }
            return strtolower(trim($v));
        };
        $header = $rows[0] ?? [];
        foreach ($header as $i => $h) {
            $header[$i] = $normalize((string) $h, $i === 0);
        }

        $find = function (array $names) use ($header) {
            foreach ($names as $n) {
                $i = array_search($n, $header, true);
                if ($i !== false) return $i;
            }
            return false;
        };

        // required
        $iOffice = $find(['office', 'name', 'office_name', 'location']);
        if ($iOffice === false) {
            $this->command?->error("CSV header not found. Detected: " . implode(', ', $header) . ". Expected an 'office' column.");
            return;
        }

        // optional
        $iAddress   = $find(['address', 'addr']);
        $iCity      = $find(['city', 'town']);
        $iState     = $find(['state', 'region_state']);
        $iPin       = $find(['pin', 'pincode', 'zipcode', 'zip']);
        $iEmail     = $find(['email', 'mail']);
        $iPhone     = $find(['phone', 'tel', 'mobile']);
        $iFax       = $find(['fax']);
        $iCountry   = $find(['country']);
        $iLat       = $find(['latitude', 'lat']);
        $iLng       = $find(['longitude', 'lng', 'long']);
        $iSortId    = $find(['sort_id', 'sort', 'order']);
        $iParentId  = $find(['parent_id', 'parent']);
        $iIsActive  = $find(['is_active', 'active', 'status']);
        // $iType      = $find(['type', 'office_type']); // <— new

        // // map any human inputs to codes
        // $normalizeType = function (?string $raw): string {
        //     $v = strtolower(trim((string) $raw));
        //     return match (true) {
        //         $v === 'ho' || $v === 'head' || $v === 'head office' => 'HO',
        //         $v === 'regional' || $v === 'ro' || $v === 'regional office' => 'RO',
        //         $v === 'chapter' || $v === 'co' || $v === 'chapter office' => 'CO',
        //         default => 'CO', // default if empty/unknown
        //     };
        // };

        $created = 0;
        $updated = 0;

        foreach (array_slice($rows, 1) as $r) {
            if (! is_array($r) || ! array_key_exists($iOffice, $r)) continue;

            $office = trim((string) ($r[$iOffice] ?? ''));
            if ($office === '') continue;

            $activeRaw = $iIsActive !== false ? ($r[$iIsActive] ?? 'true') : 'true';

            // $type = $iType !== false ? $normalizeType($r[$iType] ?? null) : 'REGIONAL';

            $payload = [
                'office'     => $office,
                // 'type'       => $type,
                'address'    => $iAddress  !== false ? (trim((string) ($r[$iAddress]  ?? '')) ?: null) : null,
                'city'       => $iCity     !== false ? (trim((string) ($r[$iCity]     ?? '')) ?: null) : null,
                'state'      => $iState    !== false ? (trim((string) ($r[$iState]    ?? '')) ?: null) : null,
                'pin'        => $iPin      !== false ? (trim((string) ($r[$iPin]      ?? '')) ?: null) : null,
                'email'      => $iEmail    !== false ? (trim((string) ($r[$iEmail]    ?? '')) ?: null) : null,
                'phone'      => $iPhone    !== false ? (trim((string) ($r[$iPhone]    ?? '')) ?: null) : null,
                'fax'        => $iFax      !== false ? (trim((string) ($r[$iFax]      ?? '')) ?: null) : null,
                'country'    => $iCountry  !== false ? ((trim((string) ($r[$iCountry] ?? ''))) ?: 'India') : 'India',
                'latitude'   => $iLat      !== false && is_numeric($r[$iLat] ?? null) ? (float) $r[$iLat] : null,
                'longitude'  => $iLng      !== false && is_numeric($r[$iLng] ?? null) ? (float) $r[$iLng] : null,
                'sort_id'    => $iSortId   !== false && is_numeric($r[$iSortId]   ?? null) ? (int) $r[$iSortId]   : 0,
                'parent_id'  => $iParentId !== false && is_numeric($r[$iParentId] ?? null) ? (int) $r[$iParentId] : null,
                'is_active'  => $this->toBool($activeRaw, true),
            ];

            $exists = \Illuminate\Support\Facades\DB::table('offices')->where('office', $office)->exists();

            if ($exists) {
                \Illuminate\Support\Facades\DB::table('offices')
                    ->where('office', $office)
                    ->update($payload + ['updated_at' => now()]);
                $updated++;
            } else {
                \Illuminate\Support\Facades\DB::table('offices')
                    ->insert($payload + ['created_at' => now(), 'updated_at' => now()]);
                $created++;
            }
        }

        $this->command?->info("Offices seeded — created: $created, updated: $updated.");
    }


    private function seedDepartments(): void
    {
        $csv = database_path('seeders/data/departments.csv'); // expected headers (any order):
        // department, long_title, short_title, sort_id, parent_id, office_id, is_active

        if (! file_exists($csv)) {
            $this->command?->warn("Departments CSV not found at: $csv");
            return;
        }

        if (! Schema::hasTable('departments')) {
            $this->command?->warn("Table 'departments' not found. Run the migration, then re-run seeder.");
            return;
        }

        $lines = file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            $this->command?->warn('Departments CSV is empty.');
            return;
        }

        // Parse CSV assuming comma delimiter first; fallback to semicolon
        $rows = array_map(fn($line) => str_getcsv($line, ','), $lines);
        if (count($rows) && count($rows[0]) === 1 && str_contains((string) $rows[0][0], ';')) {
            $rows = array_map(fn($line) => str_getcsv($line, ';'), $lines);
        }

        // Normalize headers
        $normalize = function (string $v, bool $stripBom = false): string {
            if ($stripBom) {
                $v = preg_replace('/^\xEF\xBB\xBF/', '', $v); // remove UTF-8 BOM
            }
            return strtolower(trim($v));
        };
        $header = $rows[0] ?? [];
        if (! $header) {
            $this->command?->error('CSV header row missing.');
            return;
        }
        $header = array_values($header);
        foreach ($header as $i => $h) {
            $header[$i] = $normalize((string) $h, $i === 0);
        }

        // Helper to find a column by any of several candidate names
        $findIndex = function (array $candidates) use ($header): int|false {
            foreach ($candidates as $c) {
                $i = array_search($c, $header, true);
                if ($i !== false) return $i;
            }
            return false;
        };

        // Required
        $iDepartment = $findIndex(['department', 'department_name', 'dept', 'name']);
        if ($iDepartment === false) {
            $this->command?->error("CSV header not found. Detected: " . implode(', ', $header) . ". Expected a 'department' column.");
            return;
        }

        // Optional columns
        $iLongTitle = $findIndex(['long_title', 'desc']);
        $iShortTitle  = $findIndex(['short_title', 'short', 'abbr', 'abbreviation']);
        $iSortId      = $findIndex(['sort_id', 'sortid', 'sort']);
        $iParentId    = $findIndex(['parent_id', 'parent']);
        $iOfficeId    = $findIndex(['office_id', 'office']);
        $iIsActive    = $findIndex(['is_active', 'active', 'status']);
        $iType    = $findIndex(['type', 'office_type']);
        $iRegion  = $findIndex(['region']); // <--- Added finding 'region' column
        $iGstin   = $findIndex(['gstin', 'gst', 'gst_no']);

        $created = 0;
        $updated = 0;

        // Iterate rows
        foreach (array_slice($rows, 1) as $r) {
            // Guard against ragged lines
            if (! is_array($r) || ! array_key_exists($iDepartment, $r)) continue;

            $department = trim((string) ($r[$iDepartment] ?? ''));
            if ($department === '') continue;

            $longTitle = $iLongTitle !== false ? trim((string) ($r[$iLongTitle] ?? '')) : null;
            $shortTitle  = $iShortTitle  !== false ? trim((string) ($r[$iShortTitle]  ?? '')) : null;
            $sortIdRaw   = $iSortId      !== false ? ($r[$iSortId]      ?? null) : null;
            $parentIdRaw = $iParentId    !== false ? ($r[$iParentId]    ?? null) : null;
            $officeIdRaw = $iOfficeId    !== false ? ($r[$iOfficeId]    ?? null) : null;
            $isActiveRaw = $iIsActive    !== false ? ($r[$iIsActive]    ?? null) : null;
            $typeRaw  = $iType  !== false ? ($r[$iType]  ?? null) : null;
            $regionRaw = $iRegion !== false ? ($r[$iRegion] ?? null) : null; // <--- Added extraction
            $gstinRaw = $iGstin !== false ? ($r[$iGstin] ?? null) : null;

            $type  = $typeRaw  !== null ? trim((string) $typeRaw)  : null;
            $region = $regionRaw !== null ? trim((string) $regionRaw) : null; // <--- Added processing
            $gstin = $gstinRaw !== null ? trim((string) $gstinRaw) : null;


            $sortId   = is_numeric($sortIdRaw)   ? (int) $sortIdRaw   : 0;
            $parentId = is_numeric($parentIdRaw) ? (int) $parentIdRaw : null;
            $officeId = is_numeric($officeIdRaw) ? (int) $officeIdRaw : null;
            $isActive = $this->toBool($isActiveRaw, true);

            // Prepare payload
            $payload = [
                'department'  => $department,
                'long_title' => $longTitle !== '' ? $longTitle : null,
                'short_title' => $shortTitle !== '' ? $shortTitle : null,
                'type'      => $type,
                'region'    => $region, // <--- Added to payload
                'gstin'     => $gstin,
                'sort_id'     => $sortId,
                'parent_id'   => $parentId,
                'office_id'   => $officeId,
                'is_active'   => $isActive,
            ];

            $exists = DB::table('departments')->where('department', $department)->exists();

            if ($exists) {
                DB::table('departments')
                    ->where('department', $department)
                    ->update($payload + ['updated_at' => now()]);
                $updated++;
            } else {
                DB::table('departments')
                    ->insert($payload + ['created_at' => now(), 'updated_at' => now()]);
                $created++;
            }
        }

        $this->command?->info("Departments seeded — created: $created, updated: $updated.");
    }

    // Designation Seeder

    private function seedDesignations(): void
    {
        $csv = database_path('seeders/data/designations.csv');
        if (! file_exists($csv)) {
            $this->command?->warn("Designations CSV not found at: $csv");
            return;
        }

        if (! Schema::hasTable('designations')) {
            $this->command?->warn("Table 'designations' not found. Run the migration, then re-run seeder.");
            return;
        }

        // Read lines (skip blanks)
        $lines = file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            $this->command?->warn('Designations CSV is empty.');
            return;
        }

        // Parse with comma first, fallback to semicolon
        $rows = array_map(fn($line) => str_getcsv($line, ','), $lines);
        if (count($rows) && count($rows[0]) === 1 && str_contains((string) $rows[0][0], ';')) {
            $rows = array_map(fn($line) => str_getcsv($line, ';'), $lines);
        }

        // Normalize header (lowercase, trim, strip UTF-8 BOM on first cell)
        $header = $rows[0] ?? [];
        foreach ($header as $i => $h) {
            $h = (string) $h;
            if ($i === 0) {
                $h = preg_replace('/^\xEF\xBB\xBF/u', '', $h); // remove BOM
            }
            $header[$i] = strtolower(trim($h));
        }

        // Consider it a header row if it contains any known column name
        $hasHeader = array_intersect($header, ['sort_id', 'designation', 'long_title', 'short_title', 'seniority', 'is_officer', 'is_active']) !== [];
        if ($hasHeader) {
            $rows = array_slice($rows, 1);
        }

        $created = 0;
        $updated = 0;

        foreach ($rows as $r) {
            if (! is_array($r) || count($r) === 0) continue;

            // Expected order: designation, long_title, short_title, seniority, sort_id, is_officer, is_active
            // CSV order: sort_id, designation, long_title, short_title, seniority, is_officer, is_active
            [$sortId, $designation, $longTitle, $shortTitle, $seniority, $isOfficer, $isActive] = array_pad($r, 7, null);

            $designation = trim((string) $designation);

            // Extra guard if a header sneaks through
            if ($designation === '' || strtolower($designation) === 'designation') {
                continue;
            }

            $payload = [
                'designation'  => $designation,
                'long_title'   => $longTitle !== null ? trim($longTitle)   : null,
                'short_title'  => $shortTitle !== null ? trim($shortTitle) : null,
                'seniority'    => is_numeric($seniority) ? (int) $seniority : 0,
                'sort_id'      => is_numeric($sortId) ? (int) $sortId : 0,
                'is_officer'   => $this->toBool($isOfficer, true),
                'is_active'    => $this->toBool($isActive,  true),
                'updated_at'   => now(),
            ];

            $exists = DB::table('designations')->where('designation', $designation)->exists();

            if ($exists) {
                DB::table('designations')
                    ->where('designation', $designation)
                    ->update($payload + ['updated_at' => now()]);
                $updated++;
            } else {
                DB::table('designations')
                    ->insert($payload + ['created_at' => now(), 'updated_at' => now()]);
                $created++;
            }
        }

        $this->command?->info("Designations seeded — created: $created, updated: $updated.");
    }

    // Employee Seeder

    private function seedEmployees(): void
    {
        if (! Schema::hasTable('employees')) {
            $this->command?->warn("Table 'employees' not found. Run the migration, then re-run seeder.");
            return;
        }

        $csv = database_path('seeders/data/employees.csv');
        if (! file_exists($csv)) {
            $this->command?->warn("Employees CSV not found at: $csv");
            return;
        }

        // Read lines safely (skip blanks)
        $lines = file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            $this->command?->warn('Employees CSV is empty.');
            return;
        }

        // Parse with comma first; fallback to semicolon
        $rows = array_map(fn($line) => str_getcsv($line, ','), $lines);
        if (count($rows) && count($rows[0]) === 1 && str_contains((string) $rows[0][0], ';')) {
            $rows = array_map(fn($line) => str_getcsv($line, ';'), $lines);
        }

        // Normalize header (lowercase, trim, strip UTF-8 BOM on first cell)
        $header = $rows[0] ?? [];
        foreach ($header as $i => $h) {
            $h = (string) $h;
            if ($i === 0) {
                $h = preg_replace('/^\xEF\xBB\xBF/u', '', $h); // remove BOM if present
            }
            $header[$i] = strtolower(trim($h));
        }
        $idx = array_flip($header);

        // required headers
        foreach (['employee_code', 'user_id', 'designation_id', 'department_id', 'office_id', 'email', 'is_active'] as $col) {
            if (! isset($idx[$col])) {
                $this->command?->error("Employees CSV missing required column: {$col}");
                return;
            }
        }

        // Preload valid FK id sets (fast lookup)
        $validUserSet        = array_fill_keys(DB::table('users')->pluck('id')->all(), true);
        $validDesignationSet = array_fill_keys(DB::table('designations')->pluck('id')->all(), true);
        $validDepartmentSet  = array_fill_keys(DB::table('departments')->pluck('id')->all(), true);
        $validOfficeSet      = array_fill_keys(DB::table('offices')->pluck('id')->all(), true);

        $validStatus = ['confirmed', 'contractual', 'probation', 'retired', 'resigned'];

        $now = now();
        $upsert = [];
        $chain  = []; // supervisor/manager/approver (by employee_code) for pass 2
        $skippedFk = 0;
        $skippedBad = 0;

        // PASS 1 — build upsert payloads
        foreach (array_slice($rows, 1) as $r) {
            if (! is_array($r) || ! count($r)) {
                continue;
            }

            // associate row by header
            $row = [];
            foreach ($idx as $key => $i) {
                $row[$key] = $r[$i] ?? null;
            }

            $get = fn(array $row, string $key): ?string => array_key_exists($key, $row) ? trim((string) $row[$key]) : null;

            $empId         = $get($row, 'employee_code');
            $userId        = $get($row, 'user_id');
            $designationId = $get($row, 'designation_id');
            $departmentId  = $get($row, 'department_id');
            $officeId      = $get($row, 'office_id');

            // basic shape check
            if (! $empId || ! is_numeric($userId) || ! is_numeric($designationId) || ! is_numeric($departmentId) || ! is_numeric($officeId)) {
                $this->command?->warn("Skipping row (missing/invalid FK or employee_code): " . json_encode($row));
                $skippedBad++;
                continue;
            }

            // FK validity checks
            $uid   = (int) $userId;
            $did   = (int) $designationId;
            $depid = (int) $departmentId;
            $oid   = (int) $officeId;

            $bad = [];
            if (!isset($validUserSet[$uid]))         $bad[] = "user_id={$userId}";
            if (!isset($validDesignationSet[$did]))  $bad[] = "designation_id={$designationId}";
            if (!isset($validDepartmentSet[$depid])) $bad[] = "department_id={$departmentId}";
            if (!isset($validOfficeSet[$oid]))       $bad[] = "office_id={$officeId}";

            if ($bad) {
                $this->command?->warn("Skipping employee {$empId}: invalid FK(s): " . implode(', ', $bad));
                $skippedFk++;
                continue;
            }

            $sortId  = $get($row, 'sort_id');
            $salute  = $get($row, 'salutation');
            $name    = $get($row, 'name') ?: $empId;

            // status normalization
            $status  = strtolower($get($row, 'status') ?: 'confirmed');
            if (! in_array($status, $validStatus, true)) {
                $status = 'confirmed';
            }

            // gender normalization (M/F/O, Male/Female/Other → canonical)
            $genderRaw = $get($row, 'gender');
            $gender    = strtolower((string) ($genderRaw ?? ''));

            if (in_array($gender, ['m', 'male'], true)) {
                $gender = 'Male';
            } elseif (in_array($gender, ['f', 'female'], true)) {
                $gender = 'Female';
            } elseif (in_array($gender, ['o', 'other'], true)) {
                $gender = 'Other';
            } else {
                $gender = null;
            }


            $basic   = $get($row, 'basic');
            $email   = strtolower($get($row, 'email') ?: '');
            $mobile  = $get($row, 'mobile');

            $pan     = $get($row, 'pan');
            $aadhar  = $get($row, 'aadhar');
            $aadharName = $get($row, 'aadhar_name');
            $pfId    = $get($row, 'pf_id');
            $uan     = $get($row, 'uan');
            $lic     = $get($row, 'lic_id');

            $dobRaw  = $get($row, 'dob');
            $dojRaw  = $get($row, 'doj');

            $isActiveRaw = $get($row, 'is_active');

            $dob = $dobRaw && strtotime($dobRaw) ? date('Y-m-d', strtotime($dobRaw)) : null;
            $doj = $dojRaw && strtotime($dojRaw) ? date('Y-m-d', strtotime($dojRaw)) : null;

            $upsert[] = [
                'sort_id'     => is_numeric($sortId) ? (int) $sortId : null,

                'user_id'     => $uid,
                'employee_code'      => $empId,

                'salutation'  => $salute ?: null,
                'name'        => $name,
                'gender'      => $gender,
                'dob'         => $dob,
                'doj'         => $doj,

                // note: your table uses 'designation', 'department', 'office' (not *_id)
                'designation_id' => $did,
                'department_id'  => $depid,
                'office_id'      => $oid,

                'status'      => $status,
                'basic'       => $basic ?: null,

                // chain set in pass 2
                'supervisor_code'  => null,
                'manager_code'     => null,
                'approver_code'    => null,

                'email'       => $email,
                'mobile'      => $mobile ?: null,

                'pan'         => $pan ?: null,
                'aadhar'      => $aadhar ?: null,
                'aadhar_name' => $aadharName ?: null,
                'pf_id'       => $pfId ?: null,
                'uan'         => $uan ?: null,
                'lic_id'      => $lic ?: null,

                'is_active'   => $this->toBool($isActiveRaw, true),
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $chain[$empId] = [
                'supervisor_code' => $get($row, 'supervisor_code'),
                'manager_code'    => $get($row, 'manager_code'),
                'approver_code'   => $get($row, 'approver_code'),
            ];
        }

        if (! $upsert) {
            $this->command?->warn('No valid employee rows to insert.');
            if ($skippedFk > 0)  $this->command?->warn("Employees skipped due to invalid foreign keys: {$skippedFk}");
            if ($skippedBad > 0) $this->command?->warn("Employees skipped due to missing/invalid base fields: {$skippedBad}");
            return;
        }

        // Upsert in chunks (large CSV friendly)
        foreach (array_chunk($upsert, 1000) as $chunk) {
            DB::table('employees')->upsert(
                $chunk,
                ['employee_code'],
                [
                    'sort_id',
                    'user_id',
                    'salutation',
                    'name',
                    'gender',
                    'dob',
                    'doj',
                    'designation_id',
                    'department_id',
                    'office_id',
                    'status',
                    'basic',
                    'email',
                    'mobile',
                    'pan',
                    'aadhar',
                    'aadhar_name',
                    'pf_id',
                    'uan',
                    'lic_id',
                    'is_active',
                    'updated_at',
                ]
            );
        }

        // PASS 2 — link reporting chain
        $hasManagerId  = Schema::hasColumn('employees', 'manager_id');
        $hasApproverId = Schema::hasColumn('employees', 'approver_id');

        if ($hasManagerId || $hasApproverId) {
            // numeric *_id columns: map employee_code -> id
            $idByEmp = DB::table('employees')->pluck('id', 'employee_code'); // ['EMP001' => 123, ...]
            foreach ($chain as $empId => $refs) {
                $updates = ['updated_at' => now()];
                if ($hasManagerId) {
                    $updates['manager_id'] = ! empty($refs['manager_code']) && isset($idByEmp[$refs['manager_code']])
                        ? $idByEmp[$refs['manager_code']]
                        : null;
                }
                if ($hasApproverId) {
                    $updates['approver_id'] = ! empty($refs['approver_code']) && isset($idByEmp[$refs['approver_code']])
                        ? $idByEmp[$refs['approver_code']]
                        : null;
                }

                DB::table('employees')->where('employee_code', $empId)->update($updates);
            }
        } else {
            // string columns: write employee_code strings if present in table
            $hasSupervisor = Schema::hasColumn('employees', 'supervisor_code');
            $hasManager    = Schema::hasColumn('employees', 'manager_code');
            $hasApprover   = Schema::hasColumn('employees', 'approver_code');

            foreach ($chain as $empId => $refs) {
                $updates = ['updated_at' => now()];
                if ($hasSupervisor) $updates['supervisor_code'] = $refs['supervisor_code'] ?: null;
                if ($hasManager)    $updates['manager_code']    = $refs['manager_code']    ?: null;
                if ($hasApprover)   $updates['approver_code']   = $refs['approver_code']   ?: null;

                DB::table('employees')->where('employee_code', $empId)->update($updates);
            }
        }

        $this->command?->info('Employees seeded from employees.csv. Inserted/updated rows: ' . count($upsert));
        if ($skippedFk > 0)  $this->command?->warn("Employees skipped due to invalid foreign keys: {$skippedFk}");
        if ($skippedBad > 0) $this->command?->warn("Employees skipped due to missing/invalid base fields: {$skippedBad}");
    }

    private function seedCurrentAllocations(): void
    {
        $path = database_path('seeders/data/employees.csv');
        if (! file_exists($path)) {
            $this->command?->error("CSV file not found at: $path");
            return;
        }

        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows); // Remove header

        $deptCount = 0;
        $desgCount = 0;
        $basicCount = 0;

        foreach ($rows as $row) {
            // CSV Mapping based on your file structure:
            // Col 2: employee_code
            // Col 8: designation_id
            // Col 9: department_id
            // Col 12: basic

            $empCode = trim($row[2] ?? '');
            $desgId  = isset($row[8]) && $row[8] !== '' ? (int)$row[8] : null;
            $deptId  = isset($row[9]) && $row[9] !== '' ? (int)$row[9] : null;
            $basic   = isset($row[12]) && $row[12] !== '' ? (float)$row[12] : null;

            if (! $empCode) continue;

            // Find Employee by Code
            $employee = \App\Models\Employee::where('employee_code', $empCode)->first();

            if ($employee) {
                $now = now();

                // 1. Seed Department (Active)
                if ($deptId) {
                    DB::table('department_employee')->updateOrInsert(
                        [
                            'employee_id'   => $employee->id,
                            'department_id' => $deptId,
                            'to_date'       => null, // Ensure it's the active record
                        ],
                        [
                            'from_date'  => $now,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                    $deptCount++;
                }

                // 2. Seed Designation (Active)
                if ($desgId) {
                    DB::table('designation_employee')->updateOrInsert(
                        [
                            'employee_id'    => $employee->id,
                            'designation_id' => $desgId,
                            'to_date'        => null,
                        ],
                        [
                            'employee_code' => $empCode,
                            'from_date'     => $now,
                            'created_at'    => $now,
                            'updated_at'    => $now,
                        ]
                    );
                    $desgCount++;
                }

                // 3. Seed Basic Pay (Active) - NEW
                if ($basic) {
                    // Optional: Sync main table cache for performance
                    $employee->update(['basic' => $basic]);

                    DB::table('employee_basic')->updateOrInsert(
                        [
                            'employee_id' => $employee->id,
                            'to_date'     => null, // Active record
                        ],
                        [
                            'basic_pay'  => $basic,
                            'from_date'  => $now,
                            'remarks'    => 'Initial Import',
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]
                    );
                    $basicCount++;
                }
            }
        }

        $this->command->info("Seeded Allocations: $deptCount depts, $desgCount desgs, $basicCount basic pay records.");
    }

    private function toBool($value, bool $default = true): bool
    {
        if ($value === null || $value === '') return $default;
        $v = strtolower(trim((string) $value));
        if (in_array($v, ['1', 'true', 'yes', 'y', 'on'], true))  return true;
        if (in_array($v, ['0', 'false', 'no', 'n', 'off'], true)) return false;
        return $default; // fallback for anything else
    }
}
