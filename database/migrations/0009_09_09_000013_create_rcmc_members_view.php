<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS rcmc_members");
    }
};