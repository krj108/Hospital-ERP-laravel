<?php

namespace Modules\MedicalConditions\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\MedicalConditions\App\Models\MedicalCondition;
use Modules\SurgicalProcedures\App\Models\SurgicalProcedure;
use Illuminate\Support\Facades\DB; // Import DB for transactions

class MedicalConditionController extends Controller
{
    public function store(Request $request)
    {
        // Validate the input
        $request->validate([
            'patient_national_id' => 'required|exists:patients,national_id',
            'condition_description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'room_id' => 'required|exists:rooms,id',
            'services' => 'required|array', // Array of service IDs
            'services.*' => 'exists:services,id', // Validate that services exist in the services table
            'medications' => 'required|string',
            'follow_up' => 'boolean',
            'follow_up_date' => 'nullable|date',
            'doctor_id' => 'required|exists:doctors,id',
            'surgery_required' => 'boolean',
        ]);

        // Using DB transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Create the medical condition
            $medicalCondition = MedicalCondition::create($request->except('services'));

            // Attach the services to the medical condition
            $medicalCondition->services()->sync($request->services);

            // If surgery is required, validate and create the surgery details
            if ($request->surgery_required) {
                $request->validate([
                    'surgery_type' => 'required|string',
                    'surgery_department_id' => 'required|exists:departments,id',
                    'surgery_room_id' => 'required|exists:rooms,id',
                    'medical_staff' => 'required|array', // Array of doctor IDs
                    'medical_staff.*' => 'exists:doctors,id', // Validate each doctor ID exists
                ]);

                SurgicalProcedure::create([
                    'medical_condition_id' => $medicalCondition->id,
                    'surgery_type' => $request->surgery_type,
                    'department_id' => $request->surgery_department_id,
                    'room_id' => $request->surgery_room_id,
                    'medical_staff' => $request->medical_staff, // Store the array of doctor IDs
                ]);
            }

            // If everything is successful, commit the transaction
            DB::commit();

            // Return the created medical condition with related services
            return response()->json($medicalCondition->load('services'), 201);

        } catch (\Exception $e) {
            // If any error occurs, rollback the transaction
            DB::rollBack();

            // Return error response
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
}
