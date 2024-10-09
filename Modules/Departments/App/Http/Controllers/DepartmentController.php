<?php



namespace Modules\Departments\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Departments\App\Models\Department;

class DepartmentController extends Controller
{
    // Get all departments with their associated rooms
    public function index()
    {
        return Department::with('rooms')->get();
    }

    // Store a new department
    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Create the department with the provided name
        $department = Department::create($request->only('name'));

        // Return the created department with a 201 response code
        return response()->json($department, 201);
    }

    // Update an existing department
    public function update(Request $request, Department $department)
    {
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update the department with the new name
        $department->update($request->only('name'));

        // Return the updated department
        return response()->json($department);
    }

    // Delete a department
    public function destroy(Department $department)
    {
        // Delete the specified department
        $department->delete();

        // Return a 204 (No Content) response
        return response()->json(null, 204);
    }
}

