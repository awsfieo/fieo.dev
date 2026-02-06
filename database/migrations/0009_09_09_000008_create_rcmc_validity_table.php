<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_validity', function (Blueprint $table) {
            $table->id();
            
            // Key Fields
            $table->string('iec')->nullable()->index();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            $table->date('rcmc_valid_upto')->nullable();

            // Additional Data
            $table->string('epc_short_name')->nullable();
            $table->string('application_status')->nullable();
            $table->string('msme_status')->nullable();
            $table->string('star_rating')->nullable();
            
            // Financials (Decimal for currency/turnover)
            $table->decimal('annual_turnover', 20, 2)->nullable();
            $table->decimal('export_turnover', 20, 2)->nullable();
            $table->decimal('export_performance', 20, 2)->nullable();
            
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_validity');
    }
};