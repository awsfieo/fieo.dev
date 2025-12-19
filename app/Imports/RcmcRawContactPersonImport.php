<?php

namespace App\Imports;

use App\Models\RcmcRawContactPerson;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class RcmcRawContactPersonImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        return new RcmcRawContactPerson([
            'iec'             => $row['iec_number'] ?? null,
            'contact_type'    => $row['category'] ?? null, // Map Category -> contact_type
            'name'            => $row['name'] ?? null,
            'designation'     => $row['designation'] ?? null,
            
            'epc_short_name'  => $row['epc_short_name'] ?? null,
            'address_line_1'  => $row['address_line_1'] ?? null,
            'address_line_2'  => $row['address_line_2'] ?? null,
            'city'            => $row['city'] ?? null,
            'pincode'         => $row['pin'] ?? null, // Map Pin -> pincode
            'district'        => $row['district'] ?? null,
            'state'           => $row['state'] ?? null,
            
            'phone'           => $row['telephone_num'] ?? null,
            'mobile'          => $row['mobile'] ?? null,
            'email'           => $row['email'] ?? null,
            'website'         => $row['firm_web'] ?? null,
            
            'office'          => $row['issuing_epc_office'] ?? null,
            'file_number'     => $row['rcmc_file_number'] ?? null,
            'rcmc_number'     => $row['rcmc_number'] ?? null,
            'rcmc_issue_date' => $this->transformDate($row['issuance_date'] ?? null),
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