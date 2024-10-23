<?php

namespace Modules\Doctors\App\Http\Controllers;

use Modules\Auth\App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Modules\Doctors\App\Models\Doctor;
use Modules\Doctors\App\Models\Specialization;
use Modules\Departments\App\Models\Department;
use Spatie\Permission\Models\Role;

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
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            
        ]);

        $avatarPath = $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', 'public') : null;

        // Create the user (doctor)
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'avatar' => $avatarPath, 
        ]);

        // Create the doctor record and link it with the user
        $doctor = Doctor::create([
            'user_id' => $user->id,
            'department_id' => $request->department_id,
            'specialization_id' => $request->specialization_id,
        ]);

        // Assign the "doctor" role to the user
        $role = Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        $user->assignRole($role);

        return response()->json($doctor, 201);
    }

    public function update(Request $request, Doctor $doctor)
    {
        // Validate the request fields
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $doctor->user_id,
            'password' => 'sometimes|string|min:6',
            'department_id' => 'required|exists:departments,id',
            'specialization_id' => 'required|exists:specializations,id',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'admin_password' => 'required|string', // Admin password is required for security

        ]);
        
        // Get the current authenticated admin
        $admin = $request->user();

        // Check if the admin's password is correct
        if (!Hash::check($request->input('admin_password'), $admin->password)) {
            return response()->json(['error' => 'Invalid admin password.'], 403); // Return unauthorized if password is incorrect
        }

        // Strict handling for avatar update
        if ($request->hasFile('avatar')) {
            // Strict handling of old avatar deletion
            if ($doctor->user->avatar && Storage::disk('public')->exists($doctor->user->avatar)) {
                Storage::disk('public')->delete($doctor->user->avatar);
            }

            // Upload new avatar and update user avatar path
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $doctor->user->avatar = $avatarPath;
            $doctor->user->save(); // Make sure to save the user
        }

        // Handle password update only if filled
        if ($request->filled('password')) {
            $doctor->user->password = Hash::make($request->password);
            $doctor->user->save(); // Save the user after updating the password
        }

        // Update user information (except password)
        $doctor->user->update($request->only('email', 'name'));

        // Update doctor information
        $doctor->update([
            'department_id' => $request->department_id,
            'specialization_id' => $request->specialization_id,
        ]);

        return response()->json($doctor->load('user'), 200);
    }

    public function destroy(Doctor $doctor)
    {
        // Delete the user and doctor
        if ($doctor->user->avatar && Storage::disk('public')->exists($doctor->user->avatar)) {
            Storage::disk('public')->delete($doctor->user->avatar);
        }
        
        $doctor->user->delete();

        return response()->json(null, 204);
    }
}
