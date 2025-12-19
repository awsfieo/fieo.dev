<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_raw_validity', function (Blueprint $table) {
            $table->id();
            
            // All columns nullable strings for safe raw import
            $table->string('iec')->nullable();
            $table->string('file_number')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable(); // Mapped from 'File Issuance Date'
            
            $table->string('application_status')->nullable();
            $table->string('msme_status')->nullable();
            $table->string('star_rating')->nullable();
            
            $table->string('annual_turnover')->nullable();
            $table->string('export_turnover')->nullable();
            $table->string('export_performance')->nullable(); // Mapped from 'Export Performance Concerned Product'
            
            $table->date('rcmc_valid_upto')->nullable(); // Mapped from 'Expiry Date'
            
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_raw_validity');
    }
};