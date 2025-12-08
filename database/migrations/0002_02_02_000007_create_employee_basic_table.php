<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_basic', function (Blueprint $table) {
            $table->id();
            
            // Link to the employee
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            
            // The Pay Data
            $table->string('employee_code')->nullable()->index(); 
            $table->decimal('basic_pay', 12, 2); 
            
            // History Tracking
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable(); // NULL = Current Active Record
            
            // Audit / Office Order Fields
            $table->string('file_no')->nullable(); // Added as requested
            $table->string('order_no')->nullable(); 
            $table->date('order_date')->nullable();
            $table->text('remarks')->nullable();

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_basic');
    }
};