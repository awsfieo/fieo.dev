<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_raw_receipts', function (Blueprint $table) {
            $table->id();
            
            // Core Receipt Details (Renamed from Invoice)
            $table->date('receipt_date')->nullable(); // Excel: Invoice Date
            $table->string('receipt_number')->nullable()->index(); // Excel: GSTIN Invoice Number
            $table->string('gstin')->nullable(); // Excel: GSTIN/UIN of Recipient
            
            // Party Details
            $table->string('company_name')->nullable(); // Excel: Exporter / Party Name
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('place_of_supply')->nullable();
            
            // Transaction Details
            $table->text('purpose')->nullable();
            $table->string('hsn_sac_code')->nullable();
            
            // Financials
            $table->decimal('taxable_value', 15, 2)->nullable();
            $table->decimal('cgst_amount', 15, 2)->nullable();
            $table->decimal('sgst_amount', 15, 2)->nullable();
            $table->decimal('igst_amount', 15, 2)->nullable();
            $table->decimal('total_receipt_value', 15, 2)->nullable(); // Excel: Total Invoice Value
            
            // Meta Data
            $table->string('voucher_type')->nullable();
            $table->string('receipt_type')->nullable(); // Excel: Exporter / Invoice Type
            $table->string('bill_to')->nullable();
            $table->string('ship_to')->nullable();
            $table->string('email')->nullable(); // Excel: Email ID
            $table->string('pgi_ref_no')->nullable();
            $table->string('office')->nullable();
            
            // TDS & Membership Specifics
            $table->string('is_tds_paid')->nullable();
            $table->decimal('tds_amount', 15, 2)->nullable();
            $table->string('no_of_years')->nullable();
            
            // Income Split
            $table->decimal('current_membership_income', 15, 2)->nullable();
            $table->decimal('advance_second_year', 15, 2)->nullable();
            $table->decimal('advance_third_year', 15, 2)->nullable();
            $table->decimal('advance_fourth_year', 15, 2)->nullable();
            $table->decimal('advance_fifth_year', 15, 2)->nullable();

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_raw_receipts');
    }
};