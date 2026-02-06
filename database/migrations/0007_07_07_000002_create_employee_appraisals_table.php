<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employee_appraisals', function (Blueprint $table) {
            $table->id();

            // Links to existing tables
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('appraisal_id')->nullable()->constrained('appraisals'); // Nullable to allow creation before appraisal is finalized

            // Snapshot Data for Personnel Dept
            $table->string('employee_code')->index();
            $table->string('name');
            $table->integer('appraisal_year');
            $table->string('appraisal_month'); // Stores 'April' or 'October'
            
            // The Outcome
            $table->boolean('increment_granted')->default(false); // Changed to boolean
            $table->string('increment_percentage')->nullable();   // Added separate percentage col
            
            $table->string('status')->default('Pending');

            $table->timestampsTz();

            // Prevent duplicate outcome records for the same appraisal
            $table->unique('appraisal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_appraisals');
    }
};
