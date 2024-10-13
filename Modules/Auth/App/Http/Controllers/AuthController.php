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
       
           // Get the user's roles (since a user may have multiple roles, we fetch all)
           $roles = $user->getRoleNames(); // This will return a collection of roles
       
           // Return the token with user data and roles
           return response()->json([
               'access_token' => $token,
               'token_type' => 'Bearer',
               'roles' => $roles,  // Return roles with the response
               'name' => $user->name,  // Return the user's name
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

    // Update user profile
    public function updateProfile(Request $request)
    {
        // Validate the input data
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255|unique:users,email,' . $request->user()->id,
            'password' => 'sometimes|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Update the user data
        $user = $request->user();

        if ($request->has('name')) {
            $user->name = $request->input('name');
        }
        
        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        if ($request->has('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        $user->save();

        return response()->json($user, 200);
    }
}
