<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Create offices table
        Schema::create('offices', function (Blueprint $table) {
            $table->id(); // bigint pk
            $table->unsignedSmallInteger('sort_id')->default(0);
            $table->string('office');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin')->nullable();           // keep as string (leading zeros)
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('country', 64)->default('India');
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            
            $table->unsignedSmallInteger('parent_id')->nullable(); // self-reference (offices.id)
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            // indexes
            $table->index('is_active');
            // $table->index('type');
            $table->index('sort_id');
            $table->index('office');
            $table->index('parent_id');
        });

        // 2. Create designations table
        Schema::create('designations', function (Blueprint $table) {
            $table->id(); // bigint pk
            $table->unsignedSmallInteger('sort_id')->default(0);
            $table->string('designation');                // required
            $table->string('long_title')->nullable();    // nullable
            $table->string('short_title')->nullable();    // nullable
            $table->unsignedSmallInteger('seniority')->default(0); // lower = higher rank
            $table->boolean('is_officer')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            // indexes
            $table->index('is_active');
            $table->index('sort_id');
            $table->index('designation');
        });

        // 3. Create departments table
        Schema::create('departments', function (Blueprint $table) {
            $table->id();

            $table->string('department');               // consider ->unique() since you upsert by this
            $table->string('long_title')->nullable();
            $table->string('short_title')->nullable();
            $table->enum('type', ['HO', 'DO', 'RO', 'CO'])->nullable();
            $table->enum('region', ['HO', 'NR', 'ER', 'WR', 'SR'])->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('mid')->nullable();
            $table->string('url')->nullable();
            $table->unsignedSmallInteger('sort_id')->default(0);

            // Postgres-friendly FK columns:
            $table->foreignId('parent_id')->nullable()
                ->constrained('departments')->nullOnDelete()->cascadeOnUpdate();

            $table->foreignId('office_id')->nullable()
                ->constrained('offices')->nullOnDelete()->cascadeOnUpdate();

            $table->boolean('is_active')->default(true);
            $table->timestampsTz();

            // indexes
            $table->index('is_active');
            $table->index('region');
            $table->index('department'); // or ->unique() if you want to enforce uniqueness
            $table->index('parent_id');
            $table->index('office_id');
            $table->index('gstin');
            $table->index('mid');
        });

        // 4. Create employees table
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

        // CHECK constraints (Postgres) for employees
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

        // 5. Create employee_department table
        Schema::create('employee_department', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            
            // --- History & Audit Fields ---
            $table->string('employee_code')->nullable()->index(); // Snapshot of code at that time
            $table->date('from_date')->nullable();
            $table->date('to_date')->nullable();
            
            // --- Real World Order Details ---
            $table->string('file_no')->nullable();
            $table->string('office_order_no')->nullable();
            $table->date('office_order_date')->nullable();
            $table->text('remarks')->nullable(); // For special notes
            
            $table->timestampsTz();
        });

        // 6. Create employee_designation table
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

        // 7. Create employee_basic table
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to avoid foreign key constraint errors
        Schema::dropIfExists('employee_basic');
        Schema::dropIfExists('employee_designation');
        Schema::dropIfExists('employee_department');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('designations');
        Schema::dropIfExists('offices');
    }
};