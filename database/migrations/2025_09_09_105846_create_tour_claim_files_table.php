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
        Schema::dropIfExists('tour_claim_files');
    }
};
