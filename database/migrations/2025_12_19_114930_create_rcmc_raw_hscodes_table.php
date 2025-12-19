<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_raw_hscodes', function (Blueprint $table) {
            $table->id();
            
            // Core Identifiers
            $table->string('iec')->nullable()->index();
            $table->string('company_name')->nullable(); // Mapped from Entity Name
            $table->string('epc_short_name')->nullable();
            
            // Product Details
            $table->string('export_type')->nullable();
            $table->string('hs_code')->nullable();      // Mapped from Itch Code Key
            $table->text('product_description')->nullable(); // Mapped from Description of the Product
            $table->string('business_line')->nullable();
            $table->text('general_description')->nullable(); // Mapped from Description of Goods and Services
            
            // RCMC Details
            $table->string('file_number')->nullable()->index();
            $table->string('rcmc_number')->nullable()->index();
            $table->date('rcmc_issue_date')->nullable()->index();
            
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_raw_hscodes');
    }
};