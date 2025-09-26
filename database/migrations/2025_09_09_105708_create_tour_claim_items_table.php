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

            $table->timestampsTz();
            $table->softDeletesTz();

            $table->index(['tour_claim_id', 'line_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_claim_items');
    }
};
