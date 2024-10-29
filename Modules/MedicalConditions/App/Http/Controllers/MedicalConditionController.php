<?php

namespace Modules\MedicalConditions\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\MedicalConditions\App\Models\MedicalCondition;
use Modules\SurgicalProcedures\App\Models\SurgicalProcedure;
use Illuminate\Support\Facades\Auth; // Import Auth for user roles and permissions
use Illuminate\Support\Facades\DB;
use Modules\Doctors\App\Models\Doctor; // Import the Doctor model

class MedicalConditionController extends Controller
{
    // Store a new medical condition
    public function store(Request $request)
    {
        $request->validate([
            'patient_national_id' => 'required|exists:patients,national_id',
            'condition_description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'room_id' => 'required|exists:rooms,id',
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
            'medications' => 'required|string',
            'follow_up' => 'boolean',
            'follow_up_date' => 'nullable|date',
            'doctor_id' => 'required|exists:doctors,id', // Doctor ID who adds the condition
            'surgery_required' => 'boolean',
          

        ]);

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
                    'medical_staff.*' => 'exists:doctors,id',
                    'surgery_date' => 'required_if:surgery_required,true|date', // Ensure surgery_date is provided if surgery is required
                ]);

                SurgicalProcedure::create([
                    'medical_condition_id' => $medicalCondition->id,
                    'surgery_type' => $request->surgery_type,
                    'department_id' => $request->surgery_department_id,
                    'room_id' => $request->surgery_room_id,
                    'medical_staff' => $request->medical_staff,
                    'surgery_date' => $request->surgery_date,
                ]);
            }

            DB::commit();

            // Return the created medical condition with related services
            return response()->json($medicalCondition->load('services'), 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }

    // Fetch all medical conditions (Admin sees all, Doctors see their own)
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            // Admin can see all medical conditions
            $medicalConditions = MedicalCondition::with('services', 'surgery')->get();
        } else {
            // Find the doctor's ID linked to the user
            $doctor = Doctor::where('user_id', $user->id)->first();

            if (!$doctor) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Doctors can only see their own conditions
            $medicalConditions = MedicalCondition::with('services', 'surgery')->where('doctor_id', $doctor->id)->get();
        }

        return response()->json($medicalConditions, 200);
    }

    // Update a medical condition
    public function update(Request $request, MedicalCondition $medicalCondition)
    {
        $user = Auth::user();

        // Find the doctor's ID linked to the user
        $doctor = Doctor::where('user_id', $user->id)->first();

        // Check if the user is admin or the doctor who added the condition
        if ($user->hasRole('admin') || ($doctor && $doctor->id == $medicalCondition->doctor_id)) {
            $request->validate([
                'condition_description' => 'sometimes|string',
                'department_id' => 'sometimes|exists:departments,id',
                'room_id' => 'sometimes|exists:rooms,id',
                'services' => 'sometimes|array',
                'services.*' => 'exists:services,id',
                'medications' => 'sometimes|string',
                'follow_up' => 'boolean',
                'follow_up_date' => 'nullable|date',
            ]);

            $medicalCondition->update($request->all());

            if ($request->has('services')) {
                $medicalCondition->services()->sync($request->services);
            }

            if ($request->has('surgery')) {
                $medicalCondition->surgery()->sync($request->surgery);
            }

            return response()->json($medicalCondition->load('services' , 'surgery'), 200);
        } else {
            return response()->json(['error' => 'Unauthorized - You do not have permission to update this condition.'], 403);
        }
    }

    // Delete a medical condition
    public function destroy(MedicalCondition $medicalCondition)
    {
        $user = Auth::user();

        // Find the doctor's ID linked to the user
        $doctor = Doctor::where('user_id', $user->id)->first();

        // Check if the user is admin or the doctor who added the condition
        if ($user->hasRole('admin') || ($doctor && $doctor->id == $medicalCondition->doctor_id)) {
            $medicalCondition->delete();
            return response()->json(null, 204);
        } else {
            return response()->json(['error' => 'Unauthorized - You do not have permission to delete this condition.'], 403);
        }
    }
}
