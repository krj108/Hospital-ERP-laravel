<?php

namespace Modules\MedicalConditions\App\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Auth\App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\Doctors\App\Models\Doctor;
use Modules\MedicalConditions\App\Models\MedicalCondition;
use Modules\SurgicalProcedures\App\Models\SurgicalProcedure;

class MedicalConditionController extends Controller
{
    public function store(Request $request)
    {
        $user = Auth::user();
    
        if ($user->hasRole('doctor')) {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if (!$doctor) {
                return response()->json(['error' => 'Doctor profile not found for this user.'], 403);
            }
            $doctorId = $doctor->id;
        } elseif ($user->hasRole('admin')) {
            $request->validate([
                'doctor_id' => 'required|exists:doctors,id',
            ]);
            $doctorId = $request->input('doctor_id');
        } else {
            return response()->json(['error' => 'Unauthorized role.'], 403);
        }
    
        $validatedData = $request->validate([
            'patient_national_id' => 'required|exists:patients,national_id',
            'condition_description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'room_id' => 'required|exists:rooms,id',
            'services' => 'required|array|min:1',
            'services.*' => 'exists:services,id',
            'medications' => 'required|string',
            'follow_up' => 'boolean',
            'follow_up_date' => 'nullable|date',
            'surgery_required' => 'boolean',
            'surgery_date' => 'nullable|date',
            'surgery_type' => 'nullable|string',
            'surgery_department_id' => 'nullable|exists:departments,id',
            'surgery_room_id' => 'nullable|exists:rooms,id',
            'medical_staff' => 'nullable|array', // Medical staff as an array
            'medical_staff.*' => 'exists:doctors,id',
        ]);
    
        DB::beginTransaction();
    
        try {
            $medicalCondition = MedicalCondition::create([
                'patient_national_id' => $validatedData['patient_national_id'],
                'condition_description' => $validatedData['condition_description'],
                'department_id' => $validatedData['department_id'],
                'room_id' => $validatedData['room_id'],
                'medications' => $validatedData['medications'],
                'follow_up' => $validatedData['follow_up'] ?? false,
                'follow_up_date' => $validatedData['follow_up_date'] ?? null,
                'doctor_id' => $doctorId,
                'surgery_required' => $validatedData['surgery_required'],
            ]);
    
            $medicalCondition->services()->sync($validatedData['services']);
    
            if ($validatedData['surgery_required']) {
                SurgicalProcedure::create([
                    'medical_condition_id' => $medicalCondition->id,
                    'surgery_type' => $validatedData['surgery_type'],
                    'department_id' => $validatedData['surgery_department_id'],
                    'room_id' => $validatedData['surgery_room_id'],
                    'medical_staff' => $validatedData['medical_staff'], // Automatically casted to array
                    'surgery_date' => $validatedData['surgery_date'],
                ]);
            }
    
            DB::commit();
            return response()->json($medicalCondition->load('services', 'surgery'), 201);
    
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
        }
    }
    

    
    

    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->hasRole('admin')) {
            // Admin can view all medical conditions with related services and surgeries
            $medicalConditions = MedicalCondition::with('services', 'surgery')->get();
        } elseif ($user->hasRole('doctor')) {
            // Retrieve doctor id based on user id
            $doctorId = Doctor::where('user_id', $user->id)->value('id');

            if (!$doctorId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Get medical conditions assigned to this doctor or involving the doctor in the surgery team
            $medicalConditions = MedicalCondition::with('services', 'surgery')
                ->where('doctor_id', $doctorId)
                ->orWhereHas('surgery', function ($query) use ($doctorId) {
                    $query->where('medical_staff', 'LIKE', '%"'.$doctorId.'"%');
                })
                ->get();
        } else {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($medicalConditions, 200);
    }

    public function update(Request $request, $id)
{
    $validatedData = $request->validate([
        'condition_description' => 'required|string',
        'department_id' => 'required|exists:departments,id',
        'room_id' => 'required|exists:rooms,id',
        'services' => 'sometimes|array',
        'services.*' => 'exists:services,id',
        'medications' => 'required|string',
        'follow_up' => 'boolean',
        'follow_up_date' => 'nullable|date',
        'doctor_id' => 'required|exists:doctors,id',
        'surgery_required' => 'boolean',
        'surgery_date' => 'nullable|date',
        'surgery_type' => 'nullable|string',
        'surgery_department_id' => 'nullable|exists:departments,id',
        'surgery_room_id' => 'nullable|exists:rooms,id',
        'medical_staff' => 'nullable|array', // Array of doctor IDs
        'medical_staff.*' => 'exists:doctors,id',

    ]);

    DB::beginTransaction();

    try {
        // تحديث بيانات الحالة المرضية
        $medicalCondition = MedicalCondition::findOrFail($id);
        $medicalCondition->update([
            'condition_description' => $validatedData['condition_description'],
            'department_id' => $validatedData['department_id'],
            'room_id' => $validatedData['room_id'],
            'medications' => $validatedData['medications'],
            'follow_up' => $validatedData['follow_up'] ?? false,
            'follow_up_date' => $validatedData['follow_up_date'] ?? null,
            'doctor_id' => $validatedData['doctor_id'],
            'surgery_required' => $validatedData['surgery_required'],
        ]);


        $medicalCondition->services()->sync($validatedData['services'] ?? []);

 
        if ($validatedData['surgery_required']) {
       
            SurgicalProcedure::updateOrCreate(
                ['medical_condition_id' => $medicalCondition->id],
                [
                    'surgery_type' => $validatedData['surgery_type'],
                    'department_id' => $validatedData['surgery_department_id'],
                    'room_id' => $validatedData['surgery_room_id'],
                    'medical_staff' => $validatedData['medical_staff'],
                    'surgery_date' => $validatedData['surgery_date'],
                ]
            );
        } else {
     
            $medicalCondition->surgery()->delete();
        }

        DB::commit();
        return response()->json($medicalCondition->load('services', 'surgery'), 200);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => 'Something went wrong: ' . $e->getMessage()], 500);
    }
}


    public function destroy(MedicalCondition $medicalCondition)
    {
        try {
            $medicalCondition->surgery()->delete();
            $medicalCondition->delete();
            return response()->json(["message" => "Medical condition deleted successfully"], 204);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete condition: ' . $e->getMessage()], 500);
        }
    }


    ///test
    /////////////////////////testttttttttttttt
    /////errorrrrrrrrrr/////who is the reason -_- ***********
}
