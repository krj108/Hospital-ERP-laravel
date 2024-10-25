<?php

namespace Modules\Departments\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Departments\App\Models\Department;
use Illuminate\Support\Facades\Auth;

class DepartmentController extends Controller
{
    // Get all departments with their associated rooms
    public function index()
    {
        // Access check is not required here as it's open for both roles
        return Department::with('rooms')->get();
    }

    // Store a new department
    public function store(Request $request)
    {
        // Check if user is an admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create the department with the provided name
        $department = Department::create($request->only('name'));

        return response()->json($department, 201);
    }

    // Update an existing department
    public function update(Request $request, Department $department)
    {
        // Check if user is an admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update the department with the new name
        $department->update($request->only('name'));

        return response()->json($department);
    }

    // Delete a department
    public function destroy(Department $department)
    {
        // Check if user is an admin
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the specified department
        $department->delete();

        return response()->json(null, 204);
    }
}
