<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str; // for ULIDs

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

        $this->seedDesignations();
        $this->seedDepartments();  // upserts departments from CSV
        $this->seedOffices();

        // Ensure base roles exist
        Role::firstOrCreate(['name' => 'Super Admin']);
        Role::firstOrCreate(['name' => 'Employee']);


        $path = database_path('seeders/data/users.csv');
        if (! file_exists($path)) {
            $this->command->error("CSV file not found at: $path");
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
                $isInternal = str_ends_with($email, '@fieo.org');
                $isVerified = ! is_null($user->email_verified_at);

                if ($isInternal && $isVerified && ! $user->hasRole('Employee')) {
                    $user->assignRole('Employee');
                    $assigned++;
                }

                // ensure Super Admin gets assigned to admin@fieo.org
                if ($email === 'admin@fieo.org' && ! $user->hasRole('Super Admin')) {
                    $user->assignRole('Super Admin');
                }
            }
        }

        $this->command->info("Users Created: $created, Updated: $updated, Assigned 'Employee' Role: $assigned.");

        $this->seedEmployees();
    }

    private function seedDesignations(): void
    {
        $csv = database_path('seeders/data/designations.csv'); // adjust name if different
        if (! file_exists($csv)) {
            $this->command?->warn("Designations CSV not found at: $csv");
            return;
        }

        // if table not created yet, exit quietly (you asked to create seeder before migration)
        if (! Schema::hasTable('designations')) {
            $this->command?->warn("Table 'designations' not found. Run the migration, then re-run seeder.");
            return;
        }

        $rows = array_map('str_getcsv', file($csv));
        if (empty($rows)) {
            $this->command?->warn('Designations CSV is empty.');
            return;
        }

        // detect header row
        $header = array_map(fn($v) => strtolower(trim($v ?? '')), $rows[0]);
        $hasHeader = in_array('designation', $header, true);
        if ($hasHeader) {
            $rows = array_slice($rows, 1);
        }

        $created = 0;
        $updated = 0;

        foreach ($rows as $r) {
            // expected columns: designation, description, short_title, seniority, sort_id, is_officer, is_active
            if (count($r) < 1) continue;

            [$designation, $description, $shortTitle, $seniority, $sortId, $isOfficer, $isActive] =
                array_pad($r, 7, null);

            $designation = trim((string) $designation);
            if ($designation === '') continue;

            $payload = [
                'designation'  => $designation,
                'description'  => $description !== null ? trim($description) : null,
                'short_title'  => $shortTitle !== null ? trim($shortTitle) : null,
                'seniority'    => is_numeric($seniority) ? (int) $seniority : 0,
                'sort_id'      => is_numeric($sortId) ? (int) $sortId : 0,
                'is_officer'   => $this->toBool($isOfficer, true),
                'is_active'    => $this->toBool($isActive, true),
                'updated_at'   => now(),
            ];

            $exists = DB::table('designations')->where('designation', $designation)->exists();

            DB::table('designations')->updateOrInsert(
                ['designation' => $designation],
                $payload + ['created_at' => $exists ? DB::raw('created_at') : now()]
            );

            $exists ? $updated++ : $created++;
        }

        $this->command?->info("Designations seeded — created: $created, updated: $updated.");
    }

    private function toBool($value, bool $default = true): bool
    {
        if ($value === null || $value === '') return $default;
        $v = strtolower(trim((string) $value));
        return in_array($v, ['1', 'true', 'yes', 'y', 'on'], true);
    }

    private function seedDepartments(): void
    {
        $csv = database_path('seeders/data/departments.csv'); // expected headers (any order):
        // department, description, short_title, sort_id, parent_id, office_id, is_active

        // If the file is missing, just warn and return.
        if (! file_exists($csv)) {
            $this->command?->warn("Departments CSV not found at: $csv");
            return;
        }

        // If the table doesn't exist yet, don't error—just let the user migrate first.
        if (! \Illuminate\Support\Facades\Schema::hasTable('departments')) {
            $this->command?->warn("Table 'departments' not found. Run the migration, then re-run seeder.");
            return;
        }

        // Read lines safely (skip blank lines)
        $lines = file($csv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (! $lines) {
            $this->command?->warn('Departments CSV is empty.');
            return;
        }

        // Parse CSV assuming comma delimiter first
        $rows = array_map(fn($line) => str_getcsv($line, ','), $lines);

        // If it looks like a semicolon-delimited file, reparse with ';'
        if (count($rows) && count($rows[0]) === 1 && str_contains((string) $rows[0][0], ';')) {
            $rows = array_map(fn($line) => str_getcsv($line, ';'), $lines);
        }

        // Normalize a value: strip BOM from first cell, lowercase, trim
        $normalize = function (string $v, bool $stripBom = false): string {
            if ($stripBom) {
                $v = preg_replace('/^\xEF\xBB\xBF/', '', $v); // remove UTF-8 BOM
            }
            return strtolower(trim($v));
        };

        // Normalize headers
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
        $iDescription = $findIndex(['description', 'desc']);
        $iShortTitle  = $findIndex(['short_title', 'short', 'abbr', 'abbreviation']);
        $iSortId      = $findIndex(['sort_id', 'sortid', 'sort']);
        $iParentId    = $findIndex(['parent_id', 'parent']);
        $iOfficeId    = $findIndex(['office_id', 'office']);
        $iIsActive    = $findIndex(['is_active', 'active', 'status']);

        // Local bool parser (true by default if empty)
        $toBool = function ($value, bool $default = true): bool {
            if ($value === null || $value === '') return $default;
            $v = strtolower(trim((string) $value));
            if (in_array($v, ['1', 'true', 'yes', 'y', 'on'], true)) return true;
            if (in_array($v, ['0', 'false', 'no', 'n', 'off'], true)) return false;
            return $default;
        };

        $created = 0;
        $updated = 0;

        // Iterate rows
        foreach (array_slice($rows, 1) as $r) {
            // Guard against ragged lines
            if (! is_array($r) || ! array_key_exists($iDepartment, $r)) {
                continue;
            }

            $department = trim((string) ($r[$iDepartment] ?? ''));
            if ($department === '') {
                continue;
            }

            $description = $iDescription !== false ? trim((string) ($r[$iDescription] ?? '')) : null;
            $shortTitle  = $iShortTitle  !== false ? trim((string) ($r[$iShortTitle]  ?? '')) : null;
            $sortIdRaw   = $iSortId      !== false ? ($r[$iSortId]      ?? null) : null;
            $parentIdRaw = $iParentId    !== false ? ($r[$iParentId]    ?? null) : null;
            $officeIdRaw = $iOfficeId    !== false ? ($r[$iOfficeId]    ?? null) : null;
            $isActiveRaw = $iIsActive    !== false ? ($r[$iIsActive]    ?? null) : null;

            $sortId   = is_numeric($sortIdRaw)   ? (int) $sortIdRaw   : 0;
            $parentId = is_numeric($parentIdRaw) ? (int) $parentIdRaw : null;
            $officeId = is_numeric($officeIdRaw) ? (int) $officeIdRaw : null;
            $isActive = $toBool($isActiveRaw, true);

            // Prepare payload
            $payload = [
                'department'  => $department,
                'description' => $description !== '' ? $description : null,
                'short_title' => $shortTitle !== '' ? $shortTitle : null,
                'sort_id'     => $sortId,
                'parent_id'   => $parentId,
                'office_id'   => $officeId,
                'is_active'   => $isActive,
            ];

            // Upsert by department (exact match), preserving created_at on update
            $exists = \Illuminate\Support\Facades\DB::table('departments')
                ->where('department', $department)
                ->exists();

            if ($exists) {
                \Illuminate\Support\Facades\DB::table('departments')
                    ->where('department', $department)
                    ->update($payload + ['updated_at' => now()]);
                $updated++;
            } else {
                \Illuminate\Support\Facades\DB::table('departments')
                    ->insert($payload + ['created_at' => now(), 'updated_at' => now()]);
                $created++;
            }
        }

        $this->command?->info("Departments seeded — created: $created, updated: $updated.");
    }

    private function seedOffices(): void
    {
        $csv = database_path('seeders/data/offices.csv'); // headers (any order):
        // office,address,city,state,pin,email,phone,fax,country,latitude,longitude,sort_id,parent_id,is_active

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

        $created = 0;
        $updated = 0;

        foreach (array_slice($rows, 1) as $r) {
            if (! is_array($r) || ! array_key_exists($iOffice, $r)) continue;

            $office = trim((string) ($r[$iOffice] ?? ''));
            if ($office === '') continue;

            $country = $iCountry !== false ? trim((string) ($r[$iCountry] ?? '')) : 'India';
            $latRaw  = $iLat !== false ? ($r[$iLat] ?? null) : null;
            $lngRaw  = $iLng !== false ? ($r[$iLng] ?? null) : null;

            // parse boolean with safe default true
            $activeRaw = $iIsActive !== false ? ($r[$iIsActive] ?? 'true') : 'true';
            $v = strtolower(trim((string) $activeRaw));
            $isActive = in_array($v, ['0', 'false', 'no', 'n', 'off'], true) ? false
                : (in_array($v, ['1', 'true', 'yes', 'y', 'on'], true) ? true : true);

            $payload = [
                'office'     => $office,
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
                'is_active'  => $isActive, // from: $isActive = $this->toBool($activeRaw, true);
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

    private function seedEmployees(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('employees')) {
            $this->command?->warn("Table 'employees' not found. Run the migration, then re-run seeder.");
            return;
        }

        $csv = database_path('seeders/data/employees.csv');
        if (! file_exists($csv)) {
            $this->command?->warn("Employees CSV not found at: $csv");
            return;
        }

        $rows = array_map('str_getcsv', file($csv));
        if (empty($rows)) {
            $this->command?->warn('Employees CSV is empty.');
            return;
        }

        // header map (lowercased)
        $header = array_map(fn($v) => strtolower(trim((string) $v)), $rows[0]);
        $idx = array_flip($header);
        $get = fn(array $row, string $key): ?string => array_key_exists($key, $row) ? trim((string) $row[$key]) : null;

        // required headers (now that you added them)
        foreach (['emp_id', 'user_id', 'designation', 'department', 'office', 'email', 'is_active'] as $col) {
            if (! isset($idx[$col])) {
                $this->command?->error("Employees CSV missing required column: {$col}");
                return;
            }
        }

        $validStatus = ['confirmed', 'contractual', 'probation', 'retired', 'resigned'];
        $validGender = [null, 'male', 'female', 'other', 'm', 'f', 'o'];

        $now = now();
        $upsert = [];
        $chain = []; // supervisor/manager/approver (by emp_id) for pass 2

        // PASS 1 — upsert core rows
        foreach (array_slice($rows, 1) as $r) {
            if (! is_array($r) || ! count($r)) continue;

            // associate row by header
            $row = [];
            foreach ($idx as $key => $i) $row[$key] = $r[$i] ?? null;

            $empId         = $get($row, 'emp_id');
            $userId        = $get($row, 'user_id');
            $designationId = $get($row, 'designation');
            $departmentId  = $get($row, 'department');
            $officeId      = $get($row, 'office');

            if (! $empId || ! is_numeric($userId) || ! is_numeric($designationId) || ! is_numeric($departmentId) || ! is_numeric($officeId)) {
                $this->command?->warn("Skipping row (missing/invalid FK or emp_id): " . json_encode($row));
                continue;
            }

            $sortId  = $get($row, 'sort_id');
            $salute  = $get($row, 'salutation');
            $name    = $get($row, 'name') ?: $empId;
            $status  = strtolower($get($row, 'status') ?: 'confirmed');
            $gender  = strtolower((string) ($get($row, 'gender') ?? ''));
            $grade   = $get($row, 'grade');
            $email   = strtolower($get($row, 'email') ?: '');
            $mobile  = $get($row, 'mobile');

            $pan     = $get($row, 'pan');
            $aadhar  = $get($row, 'aadhar');
            $uan     = $get($row, 'uan');
            $lic     = $get($row, 'lic_id');

            $dobRaw  = $get($row, 'dob');
            $dojRaw  = $get($row, 'doj');

            $isActiveRaw = $get($row, 'is_active');

            if (! in_array($status, $validStatus, true)) $status = 'confirmed';
            if (! in_array($gender ?: null, $validGender, true)) $gender = null;

            $dob = $dobRaw && strtotime($dobRaw) ? date('Y-m-d', strtotime($dobRaw)) : null;
            $doj = $dojRaw && strtotime($dojRaw) ? date('Y-m-d', strtotime($dojRaw)) : null;

            $toBool = function ($v, bool $default = true): bool {
                if ($v === null || $v === '') return $default;
                $x = strtolower(trim((string) $v));
                if (in_array($x, ['1', 'true', 'yes', 'y', 'on'], true)) return true;
                if (in_array($x, ['0', 'false', 'no', 'n', 'off'], true)) return false;
                return $default;
            };

            $upsert[] = [
                // 'id' => Str::ulid(), // ← REMOVE: let BIGINT auto-increment

                'sort_id'     => is_numeric($sortId) ? (int) $sortId : null,

                'user_id'     => (int) $userId,
                'emp_id'      => $empId,

                'salutation'  => $salute ?: null,
                'name'        => $name,
                'gender'      => $gender ?: null,
                'dob'         => $dob,
                'doj'         => $doj,

                'designation' => (int) $designationId,
                'department'  => (int) $departmentId,
                'office'      => (int) $officeId,

                'status'      => $status,
                'grade'       => $grade ?: null,

                // chain set in pass 2
                'supervisor'  => null,
                'manager'     => null,
                'approver'    => null,

                'email'       => $email,
                'mobile'      => $mobile ?: null,

                'pan'         => $pan ?: null,
                'aadhar'      => $aadhar ?: null,
                'uan'         => $uan ?: null,
                'lic_id'      => $lic ?: null,

                'is_active'   => $toBool($isActiveRaw, true),
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            $chain[$empId] = [
                'supervisor' => $get($row, 'supervisor'),
                'manager'    => $get($row, 'manager'),
                'approver'   => $get($row, 'approver'),
            ];
        }

        if (! $upsert) {
            $this->command?->warn('No valid employee rows to insert.');
            return;
        }

        DB::table('employees')->upsert(
            $upsert,
            ['emp_id'],
            [
                'sort_id',
                'user_id',
                'salutation',
                'name',
                'gender',
                'dob',
                'doj',
                'designation',
                'department',
                'office',
                'status',
                'grade',
                'email',
                'mobile',
                'pan',
                'aadhar',
                'uan',
                'lic_id',
                'is_active',
                'updated_at'
            ]
        );

        // PASS 2 — link reporting chain
        $hasManagerId  = Schema::hasColumn('employees', 'manager_id');
        $hasApproverId = Schema::hasColumn('employees', 'approver_id');

        if ($hasManagerId || $hasApproverId) {
            // numeric *_id columns: map emp_id -> id
            $idByEmp = DB::table('employees')->pluck('id', 'emp_id'); // ['EMP001' => 123, ...]
            foreach ($chain as $empId => $refs) {
                $updates = ['updated_at' => now()];
                if ($hasManagerId)  $updates['manager_id']  = !empty($refs['manager'])  && isset($idByEmp[$refs['manager']])  ? $idByEmp[$refs['manager']]  : null;
                if ($hasApproverId) $updates['approver_id'] = !empty($refs['approver']) && isset($idByEmp[$refs['approver']]) ? $idByEmp[$refs['approver']] : null;

                DB::table('employees')->where('emp_id', $empId)->update($updates);
            }
        } else {
            // string columns: write emp_id strings if present in table
            $hasSupervisor = Schema::hasColumn('employees', 'supervisor');
            $hasManager    = Schema::hasColumn('employees', 'manager');
            $hasApprover   = Schema::hasColumn('employees', 'approver');

            foreach ($chain as $empId => $refs) {
                $updates = ['updated_at' => now()];
                if ($hasSupervisor) $updates['supervisor'] = $refs['supervisor'] ?: null;
                if ($hasManager)    $updates['manager']    = $refs['manager']    ?: null;
                if ($hasApprover)   $updates['approver']   = $refs['approver']   ?: null;

                DB::table('employees')->where('emp_id', $empId)->update($updates);
            }
        }

        $this->command?->info('Employees seeded from employees.csv.');
    }
}
