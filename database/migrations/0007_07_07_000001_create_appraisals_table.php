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
    }

    public function down(): void
    {
        Schema::dropIfExists('appraisals');
    }
};