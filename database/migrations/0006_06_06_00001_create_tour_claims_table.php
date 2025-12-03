<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_claims', function (Blueprint $table) {
            $table->id();

            // Important identifiers
            $table->string('application_no')->unique()->index();
            $table->string('sanction_order_no')->nullable()->index();

            // Ownership & Org snapshots
            $table->foreignId('employee_id')->constrained('employees');
            $table->string('employee_code', 64)->nullable()->constrained('employees')->index();
            $table->foreignId('designation_id')->nullable()->constrained('designations');
            $table->foreignId('department_id')->nullable()->constrained('departments');
            $table->foreignId('office_id')->nullable()->constrained('offices');
            $table->decimal('basic', 12, 2)->nullable()->constrained('employees');

            // Domestic vs International
            $table->enum('tour_type', ['domestic', 'international'])->index();

            // Event linkage and flags
            $table->boolean('is_event_based')->default(false)->index();
            $table->foreignId('event_id')->nullable()->constrained('events'); 

            // Header fields
            $table->text('purpose_of_tour');
            $table->string('place_of_tour', 128)->nullable();
            $table->string('posting_city', 128)->nullable();

            // Journey window
            $table->dateTimeTz('dep_datetime');
            $table->dateTimeTz('arr_datetime');

            // Advances and Control Totals
            $table->string('advance_currency', 8)->nullable();
            $table->decimal('advance_inr', 12, 2)->default(0);
            $table->decimal('advance_forex', 12, 2)->default(0);
            $table->decimal('total_expenses_inr', 12, 2)->default(0);
            $table->decimal('total_expenses_forex', 12, 2)->default(0);
            $table->decimal('amount_reimburse_inr', 12, 2)->default(0);
            $table->decimal('amount_reimburse_forex', 12, 2)->default(0);
            $table->decimal('amount_refund_inr', 12, 2)->default(0);
            $table->decimal('amount_refund_forex', 12, 2)->default(0);
            $table->json('payload_json')->nullable();

            // --- Workflow Fields (Updated) ---
            $table->string('current_state', 32)->default('draft')->index(); 
            
            // Tracks who currently has the ball.
            $table->foreignId('pending_with')->nullable()->constrained('employees'); 
            
            // Current active remarks (for quick access)
            $table->text('remarks')->nullable(); 
            
            // Full audit trail: [{datetime, action, from, to, remarks, attachments}, ...]
            $table->json('file_history')->nullable(); 

            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('closed_at')->nullable();

            $table->timestampsTz();

            // Indexes
            $table->index(['employee_id', 'submitted_at']);
            $table->index(['office_id', 'dep_datetime']);
            $table->index(['event_id']);
            $table->index('pending_with');
        });

        // Constraints
        DB::statement("ALTER TABLE tour_claims ADD CONSTRAINT tour_claims_arr_after_dep CHECK (arr_datetime >= dep_datetime)");
        DB::statement("ALTER TABLE tour_claims ADD CONSTRAINT tour_claims_advance_currency_chk CHECK (advance_currency IS NULL OR advance_currency IN ('INR','FOREIGN'))");
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_claims');
    }
};