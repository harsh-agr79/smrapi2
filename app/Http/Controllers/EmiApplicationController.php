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
            $data = $request->except([
                'applicant_citizenship_front', 
                'applicant_citizenship_back', 
                'guarantor_citizenship_front', 
                'guarantor_citizenship_back'
            ]);

            // Handle file uploads (stores in storage/app/public/emi-documents)
            $fileFields = [
                'applicant_citizenship_front', 
                'applicant_citizenship_back', 
                'guarantor_citizenship_front', 
                'guarantor_citizenship_back'
            ];

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
