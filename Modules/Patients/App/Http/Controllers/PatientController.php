<?php

// Modules/Patients/App/Http/Controllers/PatientController.php
namespace Modules\Patients\App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Modules\Auth\App\Models\User;
use Modules\Patients\App\Models\Patient;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{
    // public function __construct()
    // {
    //     // Restrict access to only admins or patient admins
    //     $this->middleware(['role:Admin|Patients Admin']);
    // }

    public function index()
    {
        return Patient::with(['user', 'addedBy'])->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'national_id' => 'required|digits:11|unique:patients,national_id',
            'residence_address' => 'required|string|max:255',
            'mobile_number' => 'required|string|max:15',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', 

        ]);
        $avatarPath = $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', 'public') : null;

        // Create user (patient)
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'avatar' => $avatarPath, 
        ]);

        // Automatically assign "Patient" role
        $role = Role::firstOrCreate(['name' => 'Patient', 'guard_name' => 'web']);
        $user->assignRole($role);

        // Create patient record
        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => $request->national_id,
            'residence_address' => $request->residence_address,
            'mobile_number' => $request->mobile_number,
            'added_by' => Auth::id(), // Admin or Patients Admin who created the patient
        ]);

        return response()->json($patient, 201);
    }

    public function update(Request $request, Patient $patient)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $patient->user_id,
            'national_id' => 'sometimes|digits:11|unique:patients,national_id,' . $patient->id,
            'residence_address' => 'sometimes|string|max:255',
            'mobile_number' => 'sometimes|string|max:15',
            'password' => 'sometimes|string|min:6',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $patient->user->update(['avatar' => $avatarPath]);
        }
        // Update user data
        $patient->user->update($request->only('email', 'name', 'password'));

        // Update patient details
        $patient->update($request->only('national_id', 'residence_address', 'mobile_number'));

        return response()->json($patient);
    }

    public function destroy(Patient $patient)
    {
        // Delete patient and user
        $patient->user->delete();

        return response()->json(null, 204);
    }
}
