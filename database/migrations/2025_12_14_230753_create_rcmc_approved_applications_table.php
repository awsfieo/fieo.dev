<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_approved_applications', function (Blueprint $table) {
            $table->id();
            $table->string('iec')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->date('file_date')->nullable();
            $table->string('file_number')->nullable()->index();
            $table->string('rcmc_number')->nullable();
            $table->string('application_type')->nullable();
            $table->string('status')->nullable();
            
            // Raw strings
            $table->string('closed_by')->nullable();
            $table->string('office')->nullable();

            // Processed Columns (Populated by Job)
            $table->string('employee_code')->nullable()->index();
            $table->unsignedBigInteger('office_id')->nullable()->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_approved_applications');
    }
};