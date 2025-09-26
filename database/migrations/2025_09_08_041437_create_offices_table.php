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
        Schema::create('offices', function (Blueprint $table) {
            $table->id(); // bigint pk
            $table->unsignedSmallInteger('sort_id')->default(0);
            $table->string('office');                    // required
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
            $table->unsignedBigInteger('parent_id')->nullable(); // self-reference (offices.id)
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
             $table->softDeletesTz(); // deleted_at

            // indexes
            $table->index('is_active');
            $table->index('sort_id');
            $table->index('office');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
