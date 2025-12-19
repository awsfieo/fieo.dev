<?php

namespace App\Imports;

use App\Models\RcmcRawHsCode;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class RcmcRawHsCodeImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        return new RcmcRawHsCode([
            'iec'                 => $row['iec_number'] ?? null,
            'company_name'        => $row['entity_name'] ?? null, // Map Entity Name
            'epc_short_name'      => $row['epc_short_name'] ?? null,
            'export_type'         => $row['export_type'] ?? null,
            'hs_code'             => $row['itch_code_key'] ?? null, // Map Itch Code Key
            'product_description' => $row['description_of_the_product'] ?? null, // Map Description of the Product
            'business_line'       => $row['business_line'] ?? null,
            'general_description' => $row['description_of_goods_and_services'] ?? null, // Map Description of Goods...
            'file_number'         => $row['rcmc_file_number'] ?? null,
            'rcmc_number'         => $row['rcmc_number'] ?? null,
            'rcmc_issue_date'     => $this->transformDate($row['issue_date'] ?? null),
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