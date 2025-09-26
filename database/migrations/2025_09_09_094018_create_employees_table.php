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
            $table->string('emp_id', 64)->unique()->index();

            // Basic profile (CSV: salutation, name, gender, dob, doj)
            $table->string('salutation', 16)->nullable();
            $table->string('name', 128);                 // kept even if users.name exists (reporting/exports)
            $table->string('gender', 16)->nullable();    // e.g. Male/Female/Other (validated via CHECK below)
            $table->date('dob')->nullable();
            $table->date('doj')->nullable();

            // Org placement (CSV: designation, department, office) -> BIGINT FK ids
            $table->foreignId('designation')->constrained('designations')->cascadeOnUpdate();
            $table->foreignId('department')->constrained('departments')->cascadeOnUpdate();
            $table->foreignId('office')->constrained('offices')->cascadeOnUpdate();

            // Employment (CSV: status, grade)
            $table->string('status', 20)->default('confirmed'); // confirmed|contractual|probation|retired|resigned
            $table->string('grade', 32)->nullable();

            // Reporting & approvals (CSV: supervisor, manager, approver) -> reference employees(emp_id)
            $table->string('supervisor', 64)->nullable();
            $table->string('manager', 64)->nullable();
            $table->string('approver', 64)->nullable();

            // Contact (CSV: email, mobile)
            $table->string('email', 191)->index();
            $table->string('mobile', 32)->nullable();

            // Statutory IDs (CSV: pan, aadhar, uan, lic_id)
            $table->string('pan', 20)->nullable()->index();
            $table->string('aadhar', 20)->nullable()->index();
            $table->string('uan', 20)->nullable()->index();
            $table->string('lic_id', 50)->nullable()->index();

            // Active (CSV: is_active)
            $table->boolean('is_active')->default(true);

            $table->timestampsTz();
            $table->softDeletesTz();
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
            FOREIGN KEY (supervisor) REFERENCES employees(emp_id)
            ON UPDATE CASCADE ON DELETE SET NULL
        ");
        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_manager_fk
            FOREIGN KEY (manager) REFERENCES employees(emp_id)
            ON UPDATE CASCADE ON DELETE SET NULL
        ");
        DB::statement("
            ALTER TABLE employees
            ADD CONSTRAINT employees_approver_fk
            FOREIGN KEY (approver) REFERENCES employees(emp_id)
            ON UPDATE CASCADE ON DELETE SET NULL
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
