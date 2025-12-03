<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
