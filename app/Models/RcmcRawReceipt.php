<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RcmcRawReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_date',
        'receipt_number',
        'gstin',
        'company_name',
        'address_line_1',
        'address_line_2',
        'city',
        'district',
        'state',
        'pincode',
        'place_of_supply',
        'purpose',
        'hsn_sac_code',
        'taxable_value',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'total_receipt_value',
        'voucher_type',
        'receipt_type',
        'bill_to',
        'ship_to',
        'email',
        'pgi_ref_no',
        'office',
        'is_tds_paid',
        'tds_amount',
        'no_of_years',
        'current_membership_income',
        'advance_second_year',
        'advance_third_year',
        'advance_fourth_year',
        'advance_fifth_year',
    ];
}