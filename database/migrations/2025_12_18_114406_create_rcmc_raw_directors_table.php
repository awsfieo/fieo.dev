<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rcmc_raw_directors', function (Blueprint $table) {
            $table->id();
            // All columns strictly nullable strings (dumping ground)
            $table->string('iec')->nullable();
            $table->string('pan')->nullable();
            $table->string('company_name')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->string('doe')->nullable(); // Date of Establishment (from DOB)
            $table->string('iec_issue_date')->nullable();
            $table->string('nature_of_concern')->nullable();
            $table->string('name')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('eou_sez')->nullable();
            $table->string('is_eou_sez')->nullable();
            $table->string('gstin')->nullable();
            $table->text('all_branch')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->string('rcmc_issue_date')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rcmc_raw_directors');
    }
};