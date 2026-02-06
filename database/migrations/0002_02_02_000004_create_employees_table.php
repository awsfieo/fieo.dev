<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();

            // ordering (CSV: sort_id)
            $table->integer('sort_id')->nullable()->index();

            // CSV: user_id, emp_id
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // BIGINT
            $table->string('employee_code', 64)->unique()->index();

            // Basic profile (CSV: salutation, name, gender, dob, doj)
            $table->string('salutation', 16)->nullable();
            $table->string('name', 128);                 // kept even if users.name exists (reporting/exports)
            $table->string('gender', 16)->nullable();    // e.g. Male/Female/Other (validated via CHECK below)
            $table->date('dob')->nullable();
            $table->date('doj')->nullable();

            // Org placement (CSV: designation, department, office) -> BIGINT FK ids
            $table->foreignId('designation_id')->constrained('designations')->cascadeOnUpdate();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnUpdate();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnUpdate();

            // Employment (CSV: status, grade)
            $table->string('status', 20)->default('confirmed'); // confirmed|contractual|probation|retired|resigned
            $table->enum('appraisal_month', ['April', 'October'])
                  ->default('April'); // Month in which annual appraisal is done
            $table->string('basic', 32)->nullable();

            // Reporting & approvals (CSV: supervisor, manager, approver) -> reference employees(emp_id)
            $table->string('supervisor_code', 64)->nullable();
            $table->string('manager_code', 64)->nullable();
            $table->string('approver_code', 64)->nullable();

            // Contact (CSV: email, mobile)
            $table->string('email', 191)->index();
            $table->string('mobile', 32)->nullable();
            $table->string('phone', 32)->nullable();
            
            // Statutory IDs (CSV: pan, aadhar, uan, lic_id)
            $table->string('pan', 20)->nullable();
            $table->string('aadhar', 20)->nullable();
            $table->string('aadhar_name', 128)->nullable();
            $table->string('pf_id', 30)->nullable();
            $table->string('uan', 20)->nullable();
            $table->string('lic_id', 10)->nullable();
            $table->string('bank_account', 20)->nullable();


            // Active (CSV: is_active)
            $table->boolean('is_active')->default(true);

            $table->timestampsTz();
        });

        // CHECK constraints (Postgres)
        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_status_chk
            CHECK (status IN ('confirmed','contractual','probation','retired','resigned'))
        ");

        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_gender_chk
            CHECK (gender IS NULL OR gender IN ('Male','Female','Other','M','F','O'))
        ");

        // Self-FKs to business key emp_id for chain-of-command
        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_supervisor_fk
            FOREIGN KEY (supervisor_code) REFERENCES employees(employee_code)
            ON UPDATE CASCADE ON DELETE SET NULL
        ");
        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_manager_fk
            FOREIGN KEY (manager_code) REFERENCES employees(employee_code)
            ON UPDATE CASCADE ON DELETE SET NULL
        ");
        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_approver_fk
            FOREIGN KEY (approver_code) REFERENCES employees(employee_code)
            ON UPDATE CASCADE ON DELETE SET NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
