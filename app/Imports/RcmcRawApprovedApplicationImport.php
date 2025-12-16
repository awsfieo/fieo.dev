<?php

namespace App\Imports;

use App\Models\RcmcRawApprovedApplication;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading; // <--- 1. NEW IMPORT
use PhpOffice\PhpSpreadsheet\Shared\Date;

// 2. ADD THE INTERFACE HERE
class RcmcRawApprovedApplicationImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        // Your mapping logic
        return new RcmcRawApprovedApplication([
            'iec'              => $row['iec'],
            'company_name'     => $row['firm_name'], 
            'file_date'        => $this->transformDate($row['file_date']),
            'file_number'      => $row['file_number'],
            'rcmc_number'      => $row['rcmc_number'],
            'application_type' => $row['application_type'],
            'status'           => $row['status'],
            'closed_by'        => $row['pending_with_closed_by'],
            'office'           => $row['epc_office'],
        ]);
    }

    // 3. ADD THIS METHOD
    public function chunkSize(): int
    {
        return 500; 
    }
    
    // Your existing transformDate method...
    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}