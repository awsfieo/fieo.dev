<?php

namespace App\Imports;

use App\Models\RcmcRawReceipt;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

class RcmcRawReceiptImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new RcmcRawReceipt([
            // Core Receipt Details
            'receipt_date'   => $this->transformDate($row['invoice_date'] ?? null),
            'receipt_number' => $row['gstin_invoice_number'] ?? null,
            'gstin'          => $row['gstinuin_of_recipient'] ?? null,

            // Party Details
            'company_name'    => $row['exporter_party_name'] ?? null,
            'address_line_1'  => $row['address_line1'] ?? null,
            'address_line_2'  => $row['address_line2'] ?? null,
            'city'            => $row['city'] ?? null,
            'district'        => $row['district'] ?? null,
            'state'           => $row['state'] ?? null,
            'pincode'         => $row['pincode'] ?? null,
            'place_of_supply' => $row['place_of_supply'] ?? null,

            // Transaction Details
            'purpose'      => $row['purpose'] ?? null,
            'hsn_sac_code' => $row['hsn_sac_code'] ?? null,

            // Financials
            'taxable_value'       => $row['taxable_value'] ?? null,
            'cgst_amount'         => $row['cgst_tax'] ?? null,
            'sgst_amount'         => $row['sgstutgst_tax'] ?? null,
            'igst_amount'         => $row['igst_tax'] ?? null,
            'total_receipt_value' => $row['total_invoice_value'] ?? null,

            // Meta Data
            'voucher_type' => $row['voucher_type'] ?? null,
            'receipt_type' => $row['exporter_invoice_type'] ?? null,
            'bill_to'      => $row['bill_to'] ?? null,
            'ship_to'      => $row['ship_to'] ?? null,
            'email'        => $row['email_id'] ?? null,
            'pgi_ref_no'   => $row['pgi_ref_no_transaction_id'] ?? null,
            'office'       => $row['office'] ?? null,

            // TDS & Membership
            'is_tds_paid' => $row['is_tds_paid'] ?? null,
            'tds_amount'  => $row['tds_amount'] ?? null,
            'no_of_years' => $row['no_of_years'] ?? null,

            // Income Split
            'current_membership_income' => $row['current_membership_income'] ?? null,
            'advance_second_year'       => $row['advance_to_be_carry_forwardfor_second_year'] ?? null,
            'advance_third_year'        => $row['advance_to_be_carry_forwardfor_third_year'] ?? null,
            'advance_fourth_year'       => $row['advance_to_be_carry_forwardfor_fourth_year'] ?? null,
            'advance_fifth_year'        => $row['advance_to_be_carry_forwardfor_fifth_year'] ?? null,
        ]);
    }

    public function chunkSize(): int
    {
        return 500; // Matches your approved application import
    }

    private function transformDate($value)
    {
        if (empty($value)) return null;

        try {
            // Case 1: Excel Serial Number (e.g., 44611)
            if (is_numeric($value)) {
                return Date::excelToDateTimeObject($value)->format('Y-m-d');
            }
            // Case 2: String date
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
}