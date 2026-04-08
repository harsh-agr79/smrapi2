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
        'applicant_citizenship_front',
        'applicant_citizenship_back',
        'applicant_father_name',
        'applicant_mother_name',
        'applicant_grandfather_name',
        'applicant_wife_name',
        'applicant_current_location',
        'applicant_phone_number',
        'applicant_email',
        'applicant_relation_with_guarantor',
        'applicant_source_of_income',

        // Guarantor Information
        'guarantor_citizenship_front',
        'guarantor_citizenship_back',
        'guarantor_father_name',
        'guarantor_mother_name',
        'guarantor_grandfather_name',
        'guarantor_wife_name',
        'guarantor_current_location',
        'guarantor_phone_number',
        'guarantor_email',
        'guarantor_relation',
        'guarantor_source_of_income',
    ];

    /**
     * Get the product associated with the EMI application.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
