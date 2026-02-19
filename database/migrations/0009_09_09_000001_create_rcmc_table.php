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
        // 1. Create rcmc_raw_approved_applications table
        Schema::create('rcmc_raw_approved_applications', function (Blueprint $table) {
            $table->id();
            $table->string('iec')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->date('file_date')->nullable(); // Strict Date YYYY-MM-DD
            $table->string('file_number')->nullable()->index();
            $table->string('rcmc_number')->nullable();
            $table->string('application_type')->nullable();
            $table->string('application_status')->nullable();
            $table->string('closed_by')->nullable();
            $table->string('office')->nullable();
            $table->timestampsTz();
        });

        // 2. Create rcmc_approved_applications table
        Schema::create('rcmc_approved_applications', function (Blueprint $table) {
            $table->id();
            $table->string('iec')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->date('file_date')->nullable();
            $table->string('file_number')->nullable()->index();
            $table->string('rcmc_number')->nullable();
            $table->string('application_type')->nullable();
            $table->string('application_status')->nullable();
            
            // Raw strings
            $table->string('closed_by')->nullable();
            $table->string('office')->nullable();

            // Processed Columns (Populated by Job)
            $table->string('employee_code')->nullable()->index();
            $table->unsignedBigInteger('office_id')->nullable()->index();

            $table->timestamps();
        });

        // 3. Create rcmc_raw_receipts table
        Schema::create('rcmc_raw_receipts', function (Blueprint $table) {
            $table->id();
            
            // Core Receipt Details (Renamed from Invoice)
            $table->date('receipt_date')->nullable(); // Excel: Invoice Date
            $table->string('receipt_number')->nullable()->index(); // Excel: GSTIN Invoice Number
            $table->string('gstin')->nullable(); // Excel: GSTIN/UIN of Recipient
            
            // Party Details
            $table->string('company_name')->nullable(); // Excel: Exporter / Party Name
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            $table->string('place_of_supply')->nullable();
            
            // Transaction Details
            $table->text('purpose')->nullable();
            $table->string('hsn_sac_code')->nullable();
            
            // Financials
            $table->decimal('taxable_value', 15, 2)->nullable();
            $table->decimal('cgst_amount', 15, 2)->nullable();
            $table->decimal('sgst_amount', 15, 2)->nullable();
            $table->decimal('igst_amount', 15, 2)->nullable();
            $table->decimal('total_receipt_value', 15, 2)->nullable(); // Excel: Total Invoice Value
            
            // Meta Data
            $table->string('voucher_type')->nullable();
            $table->string('receipt_type')->nullable(); // Excel: Exporter / Invoice Type
            $table->string('bill_to')->nullable();
            $table->string('ship_to')->nullable();
            $table->string('email')->nullable(); // Excel: Email ID
            $table->string('pgi_ref_no')->nullable();
            $table->string('office')->nullable();
            
            // TDS & Membership Specifics
            $table->string('is_tds_paid')->nullable();
            $table->decimal('tds_amount', 15, 2)->nullable();
            $table->string('no_of_years')->nullable();
            
            // Income Split
            $table->decimal('current_membership_income', 15, 2)->nullable();
            $table->decimal('advance_second_year', 15, 2)->nullable();
            $table->decimal('advance_third_year', 15, 2)->nullable();
            $table->decimal('advance_fourth_year', 15, 2)->nullable();
            $table->decimal('advance_fifth_year', 15, 2)->nullable();

            $table->timestampsTz();
        });

        // 4. Create rcmc_receipts table
        Schema::create('rcmc_receipts', function (Blueprint $table) {
            $table->id();

            // 1. Strict Unique Key
            $table->string('receipt_number')->unique(); 
            $table->date('receipt_date');
            
            // 2. Party & Address
            $table->string('iec')->nullable()->index();
            $table->string('gstin')->nullable()->index();
            $table->string('company_name')->nullable();
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            
            // 3. Categorization
            $table->text('purpose')->nullable();
            $table->string('membership_category')->nullable(); // Extracted from Purpose
            $table->string('hsn_sac_code')->nullable();
            
            // 4. Financials (Strict Decimals)
            $table->decimal('taxable_value', 15, 2)->default(0);
            $table->decimal('cgst_amount', 15, 2)->default(0);
            $table->decimal('sgst_amount', 15, 2)->default(0);
            $table->decimal('igst_amount', 15, 2)->default(0);
            $table->decimal('total_receipt_value', 15, 2)->default(0);
            
            // 5. Meta Data
            $table->string('voucher_type')->nullable();
            $table->string('receipt_type')->nullable();
            $table->boolean('is_tds_paid')->default(false);
            $table->decimal('tds_amount', 15, 2)->default(0);
            
            // 6. Cleaned Integer
            $table->integer('no_of_years')->default(1); 

            // 7. Income Split & FY Placeholders
            $table->decimal('current_membership_income', 15, 2)->default(0);
            
            $table->string('fy_1')->nullable(); // Placeholder
            $table->decimal('advance_second_year', 15, 2)->default(0);
            $table->string('fy_2')->nullable(); // Placeholder

            $table->decimal('advance_third_year', 15, 2)->default(0);
            $table->string('fy_3')->nullable(); // Placeholder

            $table->decimal('advance_fourth_year', 15, 2)->default(0);
            $table->string('fy_4')->nullable(); // Placeholder

            $table->decimal('advance_fifth_year', 15, 2)->default(0);
            $table->string('fy_5')->nullable(); // Placeholder

            // 8. Relations
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();

            $table->timestampsTz();
        });

        // 5. Create rcmc_raw_directors table
        Schema::create('rcmc_raw_directors', function (Blueprint $table) {
            $table->id();
            // All columns strictly nullable strings (dumping ground)
            $table->string('iec')->nullable()->index();
            $table->string('pan')->nullable();
            $table->string('company_name')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->date('doe')->nullable(); // Date of Establishment (from DOB)
            $table->date('iec_issue_date')->nullable();
            $table->string('nature_of_concern')->nullable();
            $table->string('name')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('eou_sez')->nullable();
            $table->string('is_eou_sez')->nullable();
            $table->string('gstin')->nullable();
            $table->text('all_branch')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            $table->timestampsTz();
        });

        // 6. Create rcmc_directors table
        Schema::create('rcmc_directors', function (Blueprint $table) {
            $table->id();

            // Base Columns (Mapped from Excel)
            $table->string('iec')->nullable()->index();
            $table->string('pan')->nullable(); // Firm PAN
            $table->string('company_name')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->date('doe')->nullable(); // Date of Establishment
            $table->date('iec_issue_date')->nullable();
            $table->string('nature_of_concern')->nullable();
            $table->string('name')->nullable(); // Director Name
            $table->string('branch_code')->nullable();
            
            // EOU Logic
            $table->string('eou_sez')->nullable(); 
            $table->boolean('is_eou_sez')->default(0);
            
            $table->string('gstin')->nullable();
            $table->text('all_branch')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();

            // Additional Business Columns (Nullable)
            $table->string('din')->nullable();
            $table->string('director_pan')->nullable();
            $table->string('salutation')->nullable();
            $table->string('gender')->nullable();
            $table->string('dob')->nullable();
            $table->string('designation')->nullable();
            $table->string('email')->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone')->nullable();

            $table->timestampsTz();
        });

        // 7. Create rcmc_raw_validity table
        Schema::create('rcmc_raw_validity', function (Blueprint $table) {
            $table->id();
            
            // All columns nullable strings for safe raw import
            $table->string('iec')->nullable();
            $table->string('file_number')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable(); // Mapped from 'File Issuance Date'
            
            $table->string('application_status')->nullable();
            $table->string('msme_status')->nullable();
            $table->string('star_rating')->nullable();
            
            $table->string('annual_turnover')->nullable();
            $table->string('export_turnover')->nullable();
            $table->string('export_performance')->nullable(); // Mapped from 'Export Performance Concerned Product'
            
            $table->date('rcmc_valid_upto')->nullable(); // Mapped from 'Expiry Date'
            
            $table->timestampsTz();
        });

        // 8. Create rcmc_validity table
        Schema::create('rcmc_validity', function (Blueprint $table) {
            $table->id();
            
            // Key Fields
            $table->string('iec')->nullable()->index();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            $table->date('rcmc_valid_upto')->nullable();

            // Additional Data
            $table->string('epc_short_name')->nullable();
            $table->string('application_status')->nullable();
            $table->string('msme_status')->nullable();
            $table->string('star_rating')->nullable();
            
            // Financials (Decimal for currency/turnover)
            $table->decimal('annual_turnover', 20, 2)->nullable();
            $table->decimal('export_turnover', 20, 2)->nullable();
            $table->decimal('export_performance', 20, 2)->nullable();
            
            $table->timestampsTz();
        });

        // 9. Create rcmc_raw_contact_persons table
        Schema::create('rcmc_raw_contact_persons', function (Blueprint $table) {
            $table->id();
            
            // Basic Details
            $table->string('iec')->nullable();
            $table->string('contact_type')->nullable(); // Mapped from 'Category'
            $table->string('name')->nullable();
            $table->string('designation')->nullable();
            
            // Address
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable(); // Mapped from 'Pin'
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            
            // Contact Info
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // RCMC Details
            $table->string('epc_short_name')->nullable();
            $table->string('office')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            
            $table->timestampsTz();
        });

        // 10. Create rcmc_contact_persons table
        Schema::create('rcmc_contact_persons', function (Blueprint $table) {
            $table->id();
            
            // Core Identity
            $table->string('iec')->nullable()->index();
            $table->string('contact_type')->nullable(); // e.g., Authorised Representative
            
            // Personal Details (Added per request)
            $table->string('salutation')->nullable();
            $table->string('name')->nullable();
            $table->string('gender')->nullable();
            $table->date('dob')->nullable();
            $table->string('designation')->nullable();
            
            // Address
            $table->text('address_line_1')->nullable();
            $table->text('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('pincode')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            
            // Communication
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // RCMC Meta Data
            $table->string('epc_short_name')->nullable();
            $table->string('office')->nullable();
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            
            $table->timestampsTz();
        });

        // 11. Create rcmc_raw_hscodes table
        Schema::create('rcmc_raw_hscodes', function (Blueprint $table) {
            $table->id();
            
            // Core Identifiers
            $table->string('iec')->nullable()->index();
            $table->string('company_name')->nullable(); // Mapped from Entity Name
            $table->string('epc_short_name')->nullable();
            
            // Product Details
            $table->string('export_type')->nullable();
            $table->string('hs_code')->nullable();      // Mapped from Itch Code Key
            $table->text('product_description')->nullable(); // Mapped from Description of the Product
            $table->string('business_line')->nullable();
            $table->text('general_description')->nullable(); // Mapped from Description of Goods and Services
            
            // RCMC Details
            $table->string('file_number')->nullable()->index();
            $table->string('rcmc_number')->nullable()->index();
            $table->date('rcmc_issue_date')->nullable()->index();
            
            $table->timestampsTz();
        });

        // 12. Create rcmc_hscodes table
        Schema::create('rcmc_hscodes', function (Blueprint $table) {
            $table->id();
            
            // Core HS Code Data
            $table->string('iec')->nullable()->index();
            $table->string('hs_code')->nullable()->index();
            $table->string('hs_chapter')->nullable()->index(); // Derived: First 2 digits
            $table->string('itc_hscode_id')->nullable()->index(); // Derived: First 4 digits
            
            // Product Info
            $table->string('company_name')->nullable();
            $table->string('epc_short_name')->nullable();
            $table->string('export_type')->nullable();
            
            $table->text('product_description')->nullable();
            $table->string('business_line')->nullable();
            $table->text('general_description')->nullable();
            
            // Media (JSON for multiple images)
            $table->json('product_images')->nullable(); 
            
            // Unique check fields
            $table->string('file_number')->nullable();
            $table->string('rcmc_number')->nullable();
            $table->date('rcmc_issue_date')->nullable();
            
            $table->timestampsTz();
        });

        // 13. Create rcmc_members view
        DB::statement("DROP VIEW IF EXISTS rcmc_members");

        DB::statement("
            CREATE VIEW rcmc_members AS
            WITH 
            base_iecs AS (
                SELECT DISTINCT TRIM(iec) as iec
                FROM rcmc_approved_applications
                WHERE LENGTH(TRIM(iec)) = 10
            ),
            latest_rcmc AS (
                SELECT 
                    TRIM(iec) as iec,
                    UPPER(TRIM(company_name)) as company_name,
                    file_number,
                    file_date,
                    application_type,
                    office_id,
                    ROW_NUMBER() OVER(PARTITION BY TRIM(iec) ORDER BY file_date DESC, file_number DESC) as rn
                FROM rcmc_approved_applications
                WHERE LENGTH(TRIM(iec)) = 10
            ),
            unique_name_list AS (
                SELECT DISTINCT TRIM(iec) as iec, UPPER(TRIM(company_name)) as company_name
                FROM rcmc_approved_applications
                WHERE LENGTH(TRIM(iec)) = 10
            ),
            name_history AS (
                SELECT u.iec, JSON_AGG(u.company_name) as old_name
                FROM unique_name_list u
                JOIN latest_rcmc current_app ON u.iec = current_app.iec AND current_app.rn = 1
                WHERE u.company_name <> current_app.company_name
                GROUP BY u.iec
            ),
            latest_receipt AS (
                SELECT 
                    TRIM(iec) as iec,
                    UPPER(TRIM(address_line_1)) as address_line_1,
                    UPPER(TRIM(address_line_2)) as address_line_2,
                    UPPER(TRIM(city)) as city,
                    UPPER(TRIM(district)) as district,
                    UPPER(TRIM(state)) as state,
                    TRIM(pincode) as pincode,
                    gstin,
                    membership_category,
                    ROW_NUMBER() OVER(PARTITION BY TRIM(iec) ORDER BY receipt_date DESC, receipt_number DESC) as rn
                FROM rcmc_receipts
                WHERE LENGTH(TRIM(iec)) = 10
            ),
            latest_validity AS (
                SELECT 
                    TRIM(iec) as iec,
                    star_rating,
                    annual_turnover,
                    export_turnover,
                    rcmc_valid_upto,
                    ROW_NUMBER() OVER(PARTITION BY TRIM(iec) ORDER BY rcmc_issue_date DESC) as rn
                FROM rcmc_raw_validity 
                WHERE LENGTH(TRIM(iec)) = 10
            )

            SELECT 
                base.iec,
                rcmc.company_name,
                rcmc.file_number,
                rcmc.file_date,
                rcmc.application_type,
                rcmc.office_id,
                hist.old_name,
                rec.address_line_1,
                rec.address_line_2,
                rec.city,
                rec.district,
                rec.state,
                rec.pincode,
                rec.gstin,

                -- === FINAL ROBUST CATEGORY LOGIC ===
                CASE 
                    -- 1. Receipt Category (Priority)
                    WHEN rec.membership_category IS NOT NULL 
                         AND LENGTH(TRIM(rec.membership_category)) > 2 
                         AND UPPER(rec.membership_category) NOT LIKE '%CATEGORY%' 
                        THEN rec.membership_category

                    -- 2. Star Rating (HYPHEN-PROOF)
                    -- We strictly require the words ONE, TWO, THREE, FOUR, or FIVE.
                    -- This automatically rejects '-' or '0' or 'NA'
                    WHEN val.star_rating IS NOT NULL 
                         AND val.star_rating NOT LIKE '%-%' 
                         AND (
                             UPPER(val.star_rating) LIKE '%ONE%' OR 
                             UPPER(val.star_rating) LIKE '%TWO%' OR 
                             UPPER(val.star_rating) LIKE '%THREE%' OR 
                             UPPER(val.star_rating) LIKE '%FOUR%' OR 
                             UPPER(val.star_rating) LIKE '%FIVE%'
                         )
                        THEN val.star_rating || ' Category'
                    
                    -- 3. Turnover Logic (> 5 Crores)
                    WHEN (
                        CAST(NULLIF(REGEXP_REPLACE(val.annual_turnover::text, '[^0-9.]', '', 'g'), '') AS NUMERIC) > 50000000 
                        OR 
                        CAST(NULLIF(REGEXP_REPLACE(val.export_turnover::text, '[^0-9.]', '', 'g'), '') AS NUMERIC) > 50000000
                    ) THEN 'Premium Multi Product Group Category'

                    -- 4. Default Fallback
                    ELSE 'Multi Product Group Category'
                END as membership_category,
                -- ===================================

                val.star_rating,
                val.annual_turnover,
                val.export_turnover,
                val.rcmc_valid_upto

            FROM base_iecs base
            LEFT JOIN latest_rcmc rcmc ON base.iec = rcmc.iec AND rcmc.rn = 1
            LEFT JOIN latest_receipt rec ON base.iec = rec.iec AND rec.rn = 1
            LEFT JOIN name_history hist ON base.iec = hist.iec
            LEFT JOIN latest_validity val ON base.iec = val.iec AND val.rn = 1
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop in reverse order
        DB::statement("DROP VIEW IF EXISTS rcmc_members");
        Schema::dropIfExists('rcmc_hscodes');
        Schema::dropIfExists('rcmc_raw_hscodes');
        Schema::dropIfExists('rcmc_contact_persons');
        Schema::dropIfExists('rcmc_raw_contact_persons');
        Schema::dropIfExists('rcmc_validity');
        Schema::dropIfExists('rcmc_raw_validity');
        Schema::dropIfExists('rcmc_directors');
        Schema::dropIfExists('rcmc_raw_directors');
        Schema::dropIfExists('rcmc_receipts');
        Schema::dropIfExists('rcmc_raw_receipts');
        Schema::dropIfExists('rcmc_approved_applications');
        Schema::dropIfExists('rcmc_raw_approved_applications');
    }
};