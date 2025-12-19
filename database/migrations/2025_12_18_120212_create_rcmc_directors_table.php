<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_directors', function (Blueprint $table) {
            $table->id();

            // Base Columns (Mapped from Excel)
            $table->string('iec')->nullable()->index();
            $table->string('pan')->nullable(); // Firm PAN
            $table->string('company_name')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->date('doe')->nullable(); // Date of Establishment
            $table->date('iec_issue_date')->nullable();
            $table->string('nature_of_concern')->nullable();
            $table->string('name')->nullable(); // Director Name
            $table->string('branch_code')->nullable();
            
            // EOU Logic
            $table->string('eou_sez')->nullable(); 
            $table->boolean('is_eou_sez')->default(0);
            
            $table->string('gstin')->nullable();
            $table->text('all_branch')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();

            // Additional Business Columns (Nullable)
            $table->string('din')->nullable();
            $table->string('director_pan')->nullable();
            $table->string('salutation')->nullable();
            $table->string('gender')->nullable();
            $table->string('dob')->nullable();
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_directors');
    }
};