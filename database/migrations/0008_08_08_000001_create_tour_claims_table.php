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
        // 1. Create tour_claims table
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
            $table->string('pending_with', 64)->nullable();
            $table->foreign('pending_with')
                ->references('employee_code')
                ->on('employees')
                ->nullOnDelete();

            // Current active remarks (for quick access)
            $table->text('remarks')->nullable();

            // Full audit trail: [{datetime, action, from, to, remarks, attachments}, ...]
            $table->json('file_history')->nullable();

            $table->string('sanction_order_url')->nullable(); // If you generate a PDF

            // Settlement Details
            $table->date('settlement_date')->nullable();
            $table->decimal('settlement_amount', 12, 2)->nullable();
            $table->string('settlement_utr')->nullable();
            $table->text('settlement_remarks')->nullable();

            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('closed_at')->nullable();

            $table->timestampsTz();

            // Indexes
            $table->index(['employee_id', 'submitted_at']);
            $table->index(['office_id', 'dep_datetime']);
            $table->index(['event_id']);
            $table->index('pending_with');
        });

        // Constraints for tour_claims
        DB::statement("ALTER TABLE tour_claims ADD CONSTRAINT tour_claims_arr_after_dep CHECK (arr_datetime >= dep_datetime)");
        DB::statement("ALTER TABLE tour_claims ADD CONSTRAINT tour_claims_advance_currency_chk CHECK (advance_currency IS NULL OR advance_currency IN ('INR','FOREIGN'))");

        // 2. Create tour_claim_items table
        Schema::create('tour_claim_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_claim_id')->constrained('tour_claims')->cascadeOnDelete();

            // rows in the claim form
            $table->enum('line_type', [
                'stay',
                'da',
                'travel',
                'local_conveyance',
                'registration_fee',
                'visa_fee',
                'insurance',
                'misc'
            ])->index();

            $table->string('description', 512);
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();

            $table->string('currency', 8)->default('INR');
            $table->decimal('exchange_rate', 12, 6)->nullable();
            $table->decimal('amount_forex', 12, 2)->default(0);
            $table->decimal('amount_inr', 12, 2)->default(0);

            $table->json('payload_json')->nullable();
            $table->json('uploads')->nullable();

            $table->timestampsTz();

            $table->index(['tour_claim_id', 'line_type']);
        });

        // 3. Create tour_claim_files table
        Schema::create('tour_claim_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_claim_item_id')
                ->nullable()
                ->constrained('tour_claim_items')
                ->cascadeOnDelete();

            $table->enum('kind', [
                'bill',
                'receipt',
                'invoice',
                'ticket',
                'boarding_pass',
                'passport_copy',
                'visa_copy',
                'insurance_policy',
                'other'
            ])->index();

            $table->string('disk', 64)->default('public');
            $table->text('path');
            $table->string('original_name', 255)->nullable();
            $table->string('mime', 127)->nullable();
            $table->unsignedBigInteger('size')->nullable();

            $table->timestampsTz();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order to respect foreign key constraints
        Schema::dropIfExists('tour_claim_files');
        Schema::dropIfExists('tour_claim_items');
        Schema::dropIfExists('tour_claims');
    }
};