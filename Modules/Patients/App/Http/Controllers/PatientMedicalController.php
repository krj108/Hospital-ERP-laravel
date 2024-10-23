<?php

namespace Modules\Patients\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\MedicalConditions\App\Models\MedicalCondition;
use Illuminate\Support\Facades\Auth;

class PatientMedicalController extends Controller
{
    // Fetch all medical conditions for the logged-in patient
    public function index(Request $request)
    {
        // Get the logged-in user (patient)
        $user = Auth::user();

        // Ensure the user has the 'Patient' role
        if (!$user->hasRole('Patient')) {
            return response()->json(['message' => 'Unauthorized. Only patients can access this section.'], 403);
        }

        // Fetch all medical conditions for the patient based on their national ID
        $medicalConditions = MedicalCondition::where('patient_national_id', $user->patient->national_id)
            ->with(['services', 'surgery'])
            ->get();

        return response()->json($medicalConditions, 200);
    }

    // Fetch a specific medical condition by ID
    public function show($id)
    {
        // Get the logged-in user (patient)
        $user = Auth::user();

        // Ensure the user has the 'Patient' role
        if (!$user->hasRole('Patient')) {
            return response()->json(['message' => 'Unauthorized. Only patients can access this section.'], 403);
        }

        // Fetch the medical condition by ID and ensure it belongs to the logged-in patient
        $medicalCondition = MedicalCondition::where('id', $id)
            ->where('patient_national_id', $user->patient->national_id)
            ->with(['services', 'surgery'])
            ->first();

        if (!$medicalCondition) {
            return response()->json(['message' => 'Medical condition not found or access denied.'], 404);
        }

        return response()->json($medicalCondition, 200);
    }
}
