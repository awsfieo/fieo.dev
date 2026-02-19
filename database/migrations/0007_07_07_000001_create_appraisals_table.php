<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appraisals', function (Blueprint $table) {
            $table->id();

            // --- Identification ---
            $table->string('application_no')->unique()->index(); 
            $table->foreignId('employee_id')->constrained('employees');
            $table->string('employee_code', 64)->index(); 

            // --- SNAPSHOTS (Added as requested) ---
            // Captures the employee's status at the moment of creation
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('office_id')->nullable()->constrained('offices');
            $table->decimal('basic', 12, 2)->nullable(); // Matches TourClaim 'basic' column

            // --- Period & Cycle ---
            $table->integer('appraisal_year')->index(); 
            $table->enum('appraisal_month', ['April', 'October'])->index();

            // --- Workflow Status ---
            $table->string('status', 32)->default('draft')->index();
            
            // "Hot Potato": The person who currently needs to act
            $table->string('pending_with', 64)->nullable()->index();
            $table->foreign('pending_with')->references('employee_code')->on('employees')->nullOnDelete();
            
            // --- DATA COLUMNS (Encrypted) ---
            $table->longText('appraisal_form_data')->nullable(); 
            $table->longText('evaluation_form_data')->nullable();
            $table->longText('regional_head_review_data')->nullable();
            $table->longText('final_assessment_data')->nullable();

            // --- Outcome ---
            $table->text('final_increment')->nullable(); 
            $table->boolean('is_released')->default(false)->index();

            // --- Audit ---
            $table->json('file_history')->nullable(); 

            $table->timestampsTz();

            // Constraints
            $table->unique(['employee_id', 'appraisal_year', 'appraisal_month']); 
        });

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

            // --- Dates & Extensions ---
            $table->date('appraisal_start_date')->nullable();
            $table->date('appraisal_end_date')->nullable();
           $table->boolean('deadline_extension')->default(false); // Flag
            $table->date('deadline_extension_date')->nullable();   // The actual new date
            
            // The Outcome
            $table->boolean('increment_granted')->default(false); // Changed to boolean
            $table->text('increment_percentage')->nullable(); // Added separate percentage col
            
            $table->string('status')->default('Pending');

            $table->timestampsTz();

            // Prevent duplicate outcome records for the same appraisal
            $table->unique('appraisal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisals');
        Schema::dropIfExists('employee_appraisals');
    }
};