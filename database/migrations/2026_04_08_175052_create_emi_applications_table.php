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
        Schema::create('emi_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            
            // Applicant Information
            $table->string('applicant_citizenship_front')->nullable();
            $table->string('applicant_citizenship_back')->nullable();
            $table->string('applicant_father_name')->nullable();
            $table->string('applicant_mother_name')->nullable();
            $table->string('applicant_grandfather_name')->nullable();
            $table->string('applicant_wife_name')->nullable();
            $table->string('applicant_current_location')->nullable();
            $table->string('applicant_phone_number')->nullable();
            $table->string('applicant_email')->nullable();
            $table->string('applicant_relation_with_guarantor')->nullable();
            $table->string('applicant_source_of_income')->nullable();

            // Guarantor Information
            $table->string('guarantor_citizenship_front')->nullable();
            $table->string('guarantor_citizenship_back')->nullable();
            $table->string('guarantor_father_name')->nullable();
            $table->string('guarantor_mother_name')->nullable();
            $table->string('guarantor_grandfather_name')->nullable();
            $table->string('guarantor_wife_name')->nullable();
            $table->string('guarantor_current_location')->nullable();
            $table->string('guarantor_phone_number')->nullable();
            $table->string('guarantor_email')->nullable();
            $table->string('guarantor_relation')->nullable();
            $table->string('guarantor_source_of_income')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emi_applications');
    }
};
