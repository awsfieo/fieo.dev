<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_raw_contact_persons', function (Blueprint $table) {
            $table->id();
            
            // Basic Details
            $table->string('iec')->nullable();
            $table->string('contact_type')->nullable(); // Mapped from 'Category'
            $table->string('name')->nullable();
            $table->string('designation')->nullable();
            
            // Address
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable(); // Mapped from 'Pin'
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            
            // Contact Info
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // RCMC Details
            $table->string('epc_short_name')->nullable();
            $table->string('office')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_raw_contact_persons');
    }
};