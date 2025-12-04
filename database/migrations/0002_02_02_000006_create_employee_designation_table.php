<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_designation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('designation_id')->constrained('designations')->cascadeOnDelete();
            
            // --- History & Audit Fields ---
            $table->string('employee_code')->nullable()->index(); 
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            
            // --- Real World Order Details ---
            $table->string('file_no')->nullable();
            $table->string('office_order_no')->nullable();
            $table->date('office_order_date')->nullable();
            $table->text('remarks')->nullable();
            
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_designation');
    }
};