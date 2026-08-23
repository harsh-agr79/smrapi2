<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: renameColumn() requires doctrine/dbal to be installed if you're
     * on Laravel < 11. If you're on Laravel 11+, this isn't needed.
     * (composer require doctrine/dbal)
     */
    public function up(): void
    {
        // Step 1: rename existing "current_location" columns to "current_address"
        // to match the applicant/guarantor "CURRENT ADDRESS" field in the spec.
        Schema::table('emi_applications', function (Blueprint $table) {
            $table->renameColumn('applicant_current_location', 'applicant_current_address');
            $table->renameColumn('guarantor_current_location', 'guarantor_current_address');
        });

        // Step 2: add all the missing columns.
        Schema::table('emi_applications', function (Blueprint $table) {
            // ---- Applicant ----
            $table->string('applicant_full_name')->nullable()->after('product_id');
            $table->string('applicant_permanent_address')->nullable()->after('applicant_full_name');
            $table->string('applicant_occupation')->nullable()->after('applicant_source_of_income');
            $table->string('applicant_occupation_office_name')->nullable()->after('applicant_occupation');
            $table->string('applicant_occupation_office_address')->nullable()->after('applicant_occupation_office_name');
            $table->string('applicant_live_photo')->nullable()->after('applicant_citizenship_back');
            $table->string('applicant_phone_verification')->nullable()->after('applicant_live_photo');

            // ---- Guarantor ----
            $table->string('guarantor_full_name')->nullable()->after('applicant_phone_verification');
            $table->string('guarantor_permanent_address')->nullable()->after('guarantor_full_name');
            $table->string('guarantor_occupation')->nullable()->after('guarantor_source_of_income');
            $table->string('guarantor_occupation_office_name')->nullable()->after('guarantor_occupation');
            $table->string('guarantor_occupation_office_address')->nullable()->after('guarantor_occupation_office_name');
            $table->string('guarantor_live_photo')->nullable()->after('guarantor_citizenship_back');
            $table->string('guarantor_phone_verification')->nullable()->after('guarantor_live_photo');

            // ---- Reference (new section, wasn't modeled at all before) ----
            $table->string('reference_full_name')->nullable()->after('guarantor_phone_verification');
            $table->string('reference_address')->nullable()->after('reference_full_name');
            $table->string('reference_phone_number')->nullable()->after('reference_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('emi_applications', function (Blueprint $table) {
            $table->dropColumn([
                'applicant_full_name',
                'applicant_permanent_address',
                'applicant_occupation',
                'applicant_occupation_office_name',
                'applicant_occupation_office_address',
                'applicant_live_photo',
                'applicant_phone_verification',
                'guarantor_full_name',
                'guarantor_permanent_address',
                'guarantor_occupation',
                'guarantor_occupation_office_name',
                'guarantor_occupation_office_address',
                'guarantor_live_photo',
                'guarantor_phone_verification',
                'reference_full_name',
                'reference_address',
                'reference_phone_number',
            ]);
        });

        Schema::table('emi_applications', function (Blueprint $table) {
            $table->renameColumn('applicant_current_address', 'applicant_current_location');
            $table->renameColumn('guarantor_current_address', 'guarantor_current_location');
        });
    }
};