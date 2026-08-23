<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EmiApplication;
use Illuminate\Support\Facades\Log;

class EmiApplicationController extends Controller
{
    public function store(Request $request)
    {
        try {
            // All the upload ("PIC") fields for both applicant and guarantor.
            $fileFields = [
                'applicant_citizenship_front',
                'applicant_citizenship_back',
                'applicant_live_photo',
                'applicant_phone_verification',
                'guarantor_citizenship_front',
                'guarantor_citizenship_back',
                'guarantor_live_photo',
                'guarantor_phone_verification',
            ];

            $data = $request->except($fileFields);

            // Handle file uploads (stores in storage/app/public/emi-documents)
            foreach ($fileFields as $field) {
                if ($request->hasFile($field)) {
                    $data[$field] = $request->file($field)->store('emi-documents', 'public');
                }
            }

            $application = EmiApplication::create($data);

            return response()->json([
                'success' => true,
                'message' => 'EMI Application submitted successfully.',
                'data' => $application
            ], 201);

        } catch (\Exception $e) {
            Log::error('EMI Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application.'
            ], 500);
        }
    }
}