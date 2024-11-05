<?php

namespace Modules\Doctors\App\Http\Controllers;

use Modules\Auth\App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Controller;
use Modules\Doctors\App\Models\Doctor;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function index()
    {
        return Doctor::with(['user', 'department', 'specialization'])->get();
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'specialization_id' => 'required|exists:specializations,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::transaction(function () use ($validatedData, &$doctor) {
            // Handle optional avatar upload
            $avatarPath = isset($validatedData['avatar']) ? $validatedData['avatar']->store('avatars', 'public') : null;

            // Create the user and assign doctor role
            $user = User::create([
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'name' => $validatedData['name'],
                'avatar' => $avatarPath,
            ]);
            $user->assignRole(Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']));

            // Create doctor record and link it with user
            $doctor = Doctor::create([
                'user_id' => $user->id,
                'department_id' => $validatedData['department_id'],
                'specialization_id' => $validatedData['specialization_id'],
            ]);
        });

        return response()->json($doctor->load('user' , 'department', 'specialization'), 201);
    }

    public function update(Request $request, Doctor $doctor)
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . $doctor->user_id,
            'password' => 'nullable|string|min:6',
            'department_id' => 'nullable|exists:departments,id',
            'specialization_id' => 'nullable|exists:specializations,id',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);


        DB::transaction(function () use ($request, $doctor) {
            $userUpdates = array_filter($request->only(['name', 'email']));
            if ($request->filled('password')) {
                $userUpdates['password'] = Hash::make($request->password);
            }

            // Handle avatar update if provided
            if ($request->hasFile('avatar')) {
                if ($doctor->user->avatar && Storage::disk('public')->exists($doctor->user->avatar)) {
                    Storage::disk('public')->delete($doctor->user->avatar);
                }
                $userUpdates['avatar'] = $request->file('avatar')->store('avatars', 'public');
            }

            if (!empty($userUpdates)) {
                $doctor->user->update($userUpdates);

            }

            // Update doctor fields if provided
            $doctorUpdates = array_filter($request->only(['department_id', 'specialization_id']));
            if (!empty($doctorUpdates)) {
                $doctor->update($doctorUpdates);

            }
        });
        $doctor->refresh();

        return response()->json($doctor->load('user' , 'department', 'specialization'), 200);
    }

    public function destroy(Doctor $doctor)
    {
        if ($doctor->user->avatar && Storage::disk('public')->exists($doctor->user->avatar)) {
            Storage::disk('public')->delete($doctor->user->avatar);
        }
        $doctor->user->delete();
        $doctor->delete();
        return response()->json(null, 204);
    }
}
