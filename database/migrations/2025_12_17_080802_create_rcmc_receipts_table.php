<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_receipts', function (Blueprint $table) {
            $table->id();

            // 1. Strict Unique Key
            $table->string('receipt_number')->unique(); 
            $table->date('receipt_date');
            
            // 2. Party & Address
            $table->string('iec')->nullable()->index();
            $table->string('gstin')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            
            // 3. Categorization
            $table->text('purpose')->nullable();
            $table->string('membership_category')->nullable(); // Extracted from Purpose
            $table->string('hsn_sac_code')->nullable();
            
            // 4. Financials (Strict Decimals)
            $table->decimal('taxable_value', 15, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('total_receipt_value', 15, 2)->default(0);
            
            // 5. Meta Data
            $table->string('voucher_type')->nullable();
            $table->string('receipt_type')->nullable();
            $table->boolean('is_tds_paid')->default(false);
            $table->decimal('tds_amount', 15, 2)->default(0);
            
            // 6. Cleaned Integer
            $table->integer('no_of_years')->default(1); 

            // 7. Income Split & FY Placeholders
            $table->decimal('current_membership_income', 15, 2)->default(0);
            
            $table->string('fy_1')->nullable(); // Placeholder
            $table->decimal('advance_second_year', 15, 2)->default(0);
            $table->string('fy_2')->nullable(); // Placeholder

            $table->decimal('advance_third_year', 15, 2)->default(0);
            $table->string('fy_3')->nullable(); // Placeholder

            $table->decimal('advance_fourth_year', 15, 2)->default(0);
            $table->string('fy_4')->nullable(); // Placeholder

            $table->decimal('advance_fifth_year', 15, 2)->default(0);
            $table->string('fy_5')->nullable(); // Placeholder

            // 8. Relations
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_receipts');
    }
};