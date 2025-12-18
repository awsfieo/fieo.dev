<?php

namespace App\Imports;

use App\Models\RcmcRawDirector;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class RcmcRawDirectorImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        return new RcmcRawDirector([
            'iec'               => $row['iec_number'] ?? null,
            'pan'               => $row['pan'] ?? null,
            'company_name'      => $row['firm_name'] ?? null,
            'epc_short_name'    => $row['epc_short_name'] ?? null,
            'doe'               => $this->transformDate($row['date_of_birth'] ?? null),
            'iec_issue_date'    => $this->transformDate($row['iec_issuance_date'] ?? null),
            'nature_of_concern' => $row['nature_of_concern'] ?? null,
            'name'              => $row['name'] ?? null,
            'branch_code'       => $row['branch_code'] ?? null,
            'eou_sez'           => $row['eou_selected'] ?? null,
            'is_eou_sez'        => $row['eou_sez'] ?? null, 
            'gstin'             => $row['gstin_number'] ?? null,
            'all_branch'        => $row['all_branch'] ?? null,
            'file_number'       => $row['rcmc_file_number'] ?? null,
            'rcmc_number'       => $row['rcmc_number'] ?? null,
            'rcmc_issue_date'   => $this->transformDate($row['issue_date'] ?? null),
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    private function transformDate($value)
    {
        if (empty($value)) return null;
        try {
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null; 
        }
    }
}