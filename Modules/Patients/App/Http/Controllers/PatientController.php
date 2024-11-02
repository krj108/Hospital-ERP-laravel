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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


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
    $validatedData = $request->validate([
        'email' => 'required|email|unique:users,email',
        'password' => 'required|string|min:6',
        'name' => 'required|string|max:255',
        'national_id' => 'required|digits:11|unique:patients,national_id',
        'residence_address' => 'required|string|max:255',
        'mobile_number' => 'required|string|max:15',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    DB::transaction(function () use ($validatedData, &$patient) {
        // Handle optional avatar upload
        $avatarPath = isset($validatedData['avatar']) ? $validatedData['avatar']->store('avatars', 'public') : null;

        // Create the user and assign patient role
        $user = User::create([
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'name' => $validatedData['name'],
            'avatar' => $avatarPath,
        ]);
        $user->assignRole(Role::firstOrCreate(['name' => 'Patient', 'guard_name' => 'web']));

        // Create patient record and link it with user
        $patient = Patient::create([
            'user_id' => $user->id,
            'national_id' => $validatedData['national_id'],
            'residence_address' => $validatedData['residence_address'],
            'mobile_number' => $validatedData['mobile_number'],
            'added_by' => Auth::id(), // Admin or Patients Admin who created the patient
        ]);
    });

    return response()->json($patient->load('user'), 201);
}


public function update(Request $request, Patient $patient)
{
    $request->validate([
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $patient->user_id,
        'password' => 'nullable|string|min:6',
        'national_id' => 'nullable|digits:11|unique:patients,national_id,' . $patient->id,
        'residence_address' => 'nullable|string|max:255',
        'mobile_number' => 'nullable|string|max:15',
        'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    DB::transaction(function () use ($request, $patient) {

        $userUpdates = array_filter($request->only(['name', 'email']));
        if ($request->filled('password')) {
            $userUpdates['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($patient->user->avatar && Storage::disk('public')->exists($patient->user->avatar)) {
                Storage::disk('public')->delete($patient->user->avatar);
            }
            $userUpdates['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        if (!empty($userUpdates)) {
            $patient->user->update($userUpdates);
        }

        $patientUpdates = array_filter($request->only(['national_id', 'residence_address', 'mobile_number']));
        if (!empty($patientUpdates)) {
            $patient->update($patientUpdates);
        }
    });
    $patient->refresh();

    return response()->json($patient->load('user'), 200);
}


    public function destroy(Patient $patient)
    {
        // Delete patient and user
        $patient->user->delete();

        return response()->json(null, 204);
    }
}
