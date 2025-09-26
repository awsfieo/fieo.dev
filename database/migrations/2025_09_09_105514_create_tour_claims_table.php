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
        Schema::create('tour_claims', function (Blueprint $table) {
            $table->id();

            // Important identifiers
            $table->string('application_no')->unique()->index();
            $table->string('sanction_order_no')->nullable()->index();

            // ownership & org snapshots
            $table->foreignId('employee_id')->constrained('employees');
            $table->foreignId('office_id')->nullable()->constrained('offices');

            // domestic vs international
            $table->enum('tour_type', ['domestic', 'international'])->index();

            // header fields
            $table->text('purpose_of_tour');
            $table->string('event_name', 256)->nullable();
            $table->string('event_city', 128);
            $table->string('event_country', 128)->nullable();
            $table->string('posting_city', 128)->nullable();

            // journey window
            $table->dateTimeTz('dep_datetime');
            $table->dateTimeTz('arr_datetime');

            // advances and control totals
            $table->decimal('advance_inr', 12, 2)->default(0);
            $table->decimal('advance_forex', 12, 2)->default(0);
            $table->decimal('amount_claimed_inr', 12, 2)->default(0);
            $table->decimal('amount_claimed_forex', 12, 2)->default(0);
            $table->decimal('amount_net_payable_inr', 12, 2)->default(0);
            $table->decimal('amount_net_payable_forex', 12, 2)->default(0);

            // workflow
            $table->string('current_state', 32)->default('draft')->index();
            $table->json('meta_json')->nullable();
            $table->dateTimeTz('submitted_at')->nullable();
            $table->dateTimeTz('closed_at')->nullable();

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['employee_id', 'submitted_at']);
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_claims');
    }
};
