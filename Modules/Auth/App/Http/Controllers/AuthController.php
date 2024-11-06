<?php

namespace Modules\Auth\App\Http\Controllers;

use Modules\Auth\App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller; 

class AuthController extends Controller
{
    // User login
    public function login(Request $request) 
    { 
        // Validate the input data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Attempt to log in with the email and password
        $user = User::where('email', $request->email)->first();

        // Verify the user's existence and password
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid login credentials.'], 401);
        }

        // Create a token for the user
        $token = $user->createToken('auth_token')->plainTextToken;

        // Get the user's roles
        $roles = $user->getRoleNames(); // Fetch all roles
        
        // Return the token with user data and roles
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'roles' => $roles,
            'name' => $user->name,
            "id"=> $user->id,
        ]);
    }

    // User logout
    public function logout(Request $request)
    {
        // Revoke all tokens for the current user
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }

    // Get current user data
    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    // Update user profile (name or email)
    public function updateProfile(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
            'current_password' => 'required|string',  // Require current password for both name and email
            'avatar' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048', // Validate avatar

        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Get the current user
        $user = $request->user();
    
        // Verify the current password before making any changes
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['message' => 'Invalid password.'], 403);
        }
    
        // Update the name and email
        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
    
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }
        if ($request->hasFile('avatar')) {
            // Handle avatar upload
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $avatarPath;
        }
    
        $user->save();
    
        return response()->json($user, 200);
    }
    

    // Update user password
    public function updatePassword(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',  // 'password_confirmation' is required in the request
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Get the current user
        $user = $request->user();

        // Verify the current password
        if (!Hash::check($request->input('current_password'), $user->password)) {
            return response()->json(['message' => 'Current password is incorrect.'], 403);
        }

        // Update the password
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return response()->json(['message' => 'Password updated successfully.'], 200);
    }
}
