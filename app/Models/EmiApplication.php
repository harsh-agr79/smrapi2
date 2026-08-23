<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmiApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',

        // Applicant Information
        'applicant_full_name',
        'applicant_permanent_address',
        'applicant_current_address',
        'applicant_phone_number',
        'applicant_email',
        'applicant_grandfather_name',
        'applicant_father_name',
        'applicant_mother_name',
        'applicant_wife_name',
        'applicant_occupation',
        'applicant_occupation_office_name',
        'applicant_occupation_office_address',
        'applicant_citizenship_front',
        'applicant_citizenship_back',
        'applicant_live_photo',
        'applicant_phone_verification',
        'applicant_relation_with_guarantor',
        'applicant_source_of_income',

        // Guarantor Information
        'guarantor_full_name',
        'guarantor_permanent_address',
        'guarantor_current_address',
        'guarantor_phone_number',
        'guarantor_email',
        'guarantor_grandfather_name',
        'guarantor_father_name',
        'guarantor_mother_name',
        'guarantor_wife_name',
        'guarantor_occupation',
        'guarantor_occupation_office_name',
        'guarantor_occupation_office_address',
        'guarantor_citizenship_front',
        'guarantor_citizenship_back',
        'guarantor_live_photo',
        'guarantor_phone_verification',
        'guarantor_relation',
        'guarantor_source_of_income',

        // Reference Information
        'reference_full_name',
        'reference_address',
        'reference_phone_number',
    ];

    /**
     * Get the product associated with the EMI application.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}