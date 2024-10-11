<?php

namespace Modules\Doctors\App\Http\Controllers;

use Modules\Auth\App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Modules\Doctors\App\Models\Doctor;
use Modules\Doctors\App\Models\Specialization;
use Modules\Departments\App\Models\Department;

class DoctorController extends Controller
{
    public function index()
    {
        return Doctor::with(['user', 'department', 'specialization'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'specialization_id' => 'required|exists:specializations,id',
        ]);

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $request->department_id,
            'specialization_id' => $request->specialization_id,
        ]);

        return response()->json($doctor, 201);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'password' => 'sometimes|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'specialization_id' => 'required|exists:specializations,id',
        ]);

        $doctor->user->update($request->only('email', 'name', 'password'));

        $doctor->update([
            'department_id' => $request->department_id,
            'specialization_id' => $request->specialization_id,
        ]);

        return response()->json($doctor);
    }

    public function destroy(Doctor $doctor)
    {
        $doctor->user->delete(); // Deleting the user will cascade to delete the doctor

        return response()->json(null, 204);
    }
}
