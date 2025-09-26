<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id(); // bigint pk
            $table->unsignedSmallInteger('sort_id')->default(0);
            $table->string('department');               // required
            $table->string('description')->nullable();  // nullable
            $table->string('short_title')->nullable();  // nullable
            $table->enum('type', ['HO', 'Department', 'Region', 'Chapter', 'Office'])->nullable();
            $table->string('gstin', 15)->nullable();     // India GSTIN length = 15
            $table->string('mid')->nullable();     // Merchant ID for online payments
            $table->string('url')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable(); // self-reference (no FK yet)
            $table->unsignedBigInteger('office_id')->nullable(); // will reference offices later
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
             $table->softDeletesTz(); // deleted_at

            // indexes
            $table->index('is_active');
            $table->index('sort_id');
            $table->index('department');
            $table->index('parent_id');
            $table->index('office_id');
            $table->index('gstin');
            $table->index('mid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
