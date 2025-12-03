<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();

            // --- Basic Info ---
            $table->string('title')->index();
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('description')->nullable();

            // --- Dates & Times ---
            // Using timestampTz for Postgres to handle timezones correctly
            $table->timestampTz('start_at')->index();
            $table->timestampTz('end_at')->nullable();
            $table->string('timezone', 64)->default('Asia/Kolkata');

            // --- Website Listing Window ---
            $table->boolean('add_home_page_ticker')->default(false);
            $table->timestampTz('listing_start_at')->nullable();
            $table->timestampTz('listing_end_at')->nullable();

            // --- Venue ---
            $table->string('venue_name')->nullable();
            $table->string('venue_city')->nullable();
            $table->string('venue_country')->nullable();

            // --- Classification ---
            // You can cast these to Enums in your Model
            $table->string('event_type')->default('domestic'); // domestic/international
            $table->string('event_mode')->default('offline');  // online/offline/hybrid
            $table->boolean('under_mai_scheme')->default(false);

            // --- Capacity & Registration ---
            $table->unsignedInteger('capacity')->nullable();
            $table->boolean('allow_registration')->default(false);
            $table->boolean('allow_partial_payment')->default(false);

            // --- Financials ---
            $table->unsignedTinyInteger('applicable_gst')->default(0);
            
            $table->boolean('tds_deducted')->default(false);
            // Precision (5,2) allows values like '10.00' (10%) or '100.00'
            $table->decimal('tds_percentage', 5, 2)->nullable(); 

            // Flexible structure for fee slabs
            $table->json('registration_charges_json')->nullable();

            // --- Media ---
            // REMOVED: attachment_json & banner_json
            // These will be stored in the 'media' table provided by Spatie Media Library.

            // --- Owner Context (Snapshot) ---
            $table->string('employee_code', 32)->nullable()->index();
            $table->foreignId('designation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained()->nullOnDelete();

            // --- Status ---
            $table->string('status', 32)->default('draft')->index(); // Internal workflow
            $table->string('current_state', 32)->default('draft')->index(); // Public visibility
            $table->timestampTz('published_at')->nullable();

            // --- Quick Access Audit ---
            // We keep these for performance, even though ActivityLog tracks history.
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->foreignId('updated_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // --- Timestamps ---
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};