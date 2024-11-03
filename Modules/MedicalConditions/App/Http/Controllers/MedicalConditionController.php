<?php

namespace Modules\MedicalConditions\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\MedicalConditions\App\Models\MedicalCondition;
use Modules\SurgicalProcedures\App\Models\SurgicalProcedure;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MedicalConditionController extends Controller
{
    public function store(Request $request)
    {
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
            'doctor_id' => 'required|exists:doctors,id',
            'surgery_required' => 'boolean',
            'surgery_date' => 'required_if:surgery_required,true|date',
            'surgery_type' => 'required_if:surgery_required,true|string',
            'surgery_department_id' => 'required_if:surgery_required,true|exists:departments,id',
            'surgery_room_id' => 'required_if:surgery_required,true|exists:rooms,id',
            'medical_staff' => 'required|array', 
            'medical_staff.*' => 'exists:doctors,id',
        ]);

        DB::beginTransaction();
        
        try {
            // إنشاء الحالة المرضية
            $medicalCondition = MedicalCondition::create([
                'patient_national_id' => $validatedData['patient_national_id'],
                'condition_description' => $validatedData['condition_description'],
                'department_id' => $validatedData['department_id'],
                'room_id' => $validatedData['room_id'],
                'medications' => $validatedData['medications'],
                'follow_up' => $validatedData['follow_up'] ?? false,
                'follow_up_date' => $validatedData['follow_up_date'] ?? null,
                'doctor_id' => $validatedData['doctor_id'],
                'surgery_required' => $validatedData['surgery_required'],
            ]);

            $medicalCondition->services()->sync($validatedData['services']);

            if ($validatedData['surgery_required']) {
                SurgicalProcedure::create([
                    'medical_condition_id' => $medicalCondition->id,
                    'surgery_type' => $validatedData['surgery_type'],
                    'department_id' => $validatedData['surgery_department_id'],
                    'room_id' => $validatedData['surgery_room_id'],
                    'medical_staff' => $validatedData['medical_staff'],
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
            $medicalConditions = MedicalCondition::with('services', 'surgery')->get();
        } else {
            $doctor = $user->doctor;
            if (!$doctor) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }
            $medicalConditions = MedicalCondition::with('services', 'surgery')
                ->where('doctor_id', $doctor->id)
                ->get();
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

        // تحديث أو إضافة الخدمات
        $medicalCondition->services()->sync($validatedData['services'] ?? []);

        // إذا كانت العملية مطلوبة
        if ($validatedData['surgery_required']) {
            // التحقق من إنشاء أو تحديث العملية الجراحية
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
            // إذا لم تعد العملية مطلوبة، احذف السجل إذا كان موجودًا
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
}
