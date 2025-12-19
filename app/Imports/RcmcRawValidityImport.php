<?php

namespace App\Imports;

use App\Models\RcmcRawValidity;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class RcmcRawValidityImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        return new RcmcRawValidity([
            'iec'                => $row['iec_number'] ?? null,
            'file_number'        => $row['rcmc_file_number'] ?? null,
            'epc_short_name'     => $row['epc_short_name'] ?? null,
            'rcmc_number'        => $row['rcmc_number'] ?? null,
            
            // Map 'File Issuance Date' -> 'rcmc_issue_date'
            'rcmc_issue_date'    => $this->transformDate($row['file_issuance_date'] ?? null),
            
            'application_status' => $row['application_status'] ?? null,
            'msme_status'        => $row['msme_status'] ?? null,
            'star_rating'        => $row['star_rating'] ?? null,
            
            'annual_turnover'    => $row['annual_turnover'] ?? null,
            'export_turnover'    => $row['export_turnover'] ?? null,
            
            // Map 'Export Performance Concerned Product' -> 'export_performance'
            'export_performance' => $row['export_performance_concerned_product'] ?? null,
            
            // Map 'Expiry Date' -> 'rcmc_valid_upto'
            'rcmc_valid_upto'    => $this->transformDate($row['expiry_date'] ?? null),
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