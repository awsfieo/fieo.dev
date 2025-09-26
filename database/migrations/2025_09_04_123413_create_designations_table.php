<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('designations', function (Blueprint $table) {
            $table->id(); // bigint pk
            $table->unsignedSmallInteger('sort_id')->default(0);
            $table->string('designation');                // required
            $table->string('description')->nullable();    // nullable
            $table->string('short_title')->nullable();    // nullable
            $table->unsignedSmallInteger('seniority')->default(0); // lower = higher rank
            $table->boolean('is_officer')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->softDeletesTz(); // deleted_at

            // indexes
            $table->index('is_active');
            $table->index('sort_id');
            $table->index('designation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('designations');
    }
};
