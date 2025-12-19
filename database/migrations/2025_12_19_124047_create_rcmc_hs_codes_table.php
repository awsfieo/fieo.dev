<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_hscodes', function (Blueprint $table) {
            $table->id();
            
            // Core HS Code Data
            $table->string('iec')->nullable()->index();
            $table->string('hs_code')->nullable()->index();
            $table->string('hs_chapter')->nullable()->index(); // Derived: First 2 digits
            $table->string('itc_hscode_id')->nullable()->index(); // Derived: First 4 digits
            
            // Product Info
            $table->string('company_name')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->string('export_type')->nullable();
            
            $table->text('product_description')->nullable();
            $table->string('business_line')->nullable();
            $table->text('general_description')->nullable();
            
            // Media (JSON for multiple images)
            $table->json('product_images')->nullable(); 
            
            // Unique check fields
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_hscodes');
    }
};