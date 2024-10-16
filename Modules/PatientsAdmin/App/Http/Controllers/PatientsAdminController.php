<?php

namespace Modules\PatientsAdmin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Auth\App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class PatientsAdminController extends Controller
{
    // Only admins can create a Patients Admin account
    public function store(Request $request)
    {
        // Validate input data
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);
        $avatarPath = $request->hasFile('avatar') ? $request->file('avatar')->store('avatars', 'public') : null;


        // Create a new user for the Patients Admin
        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'name' => $request->name,
            'avatar' => $avatarPath,
        ]);

        // Ensure the "Patients Admin" role exists, otherwise create it
        $role = Role::firstOrCreate(['name' => 'patients admin', 'guard_name' => 'web']);

        // Assign the "Patients Admin" role to the newly created user
        $user->assignRole($role);

        return response()->json($user, 201);
    }

    // Fetch all Patients Admins (for admin use)
    public function index()
    {
        return User::role('Patients Admin')->get();
    }
}
