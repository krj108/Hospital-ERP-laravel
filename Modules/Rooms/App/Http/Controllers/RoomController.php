<?php

namespace Modules\Rooms\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Rooms\App\Models\Room;

class RoomController extends Controller
{
    // Display a listing of all rooms with their associated departments
    public function index()
    {
        return Room::with('department')->get();
    }

    // Store a newly created room in the database
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'number' => 'required|string|max:255', // Room number is required
            'status' => 'required|in:vacant,occupied,maintenance', // Status must be one of the three options
            'department_id' => 'required|exists:departments,id', // Department must exist in the departments table
        ]);

        // Create the new room with the validated data
        $room = Room::create($request->only('number', 'status', 'department_id'));

        // Return the newly created room data with a 201 status code
        return response()->json($room, 201);
    }

    // Update the specified room in the database
    public function update(Request $request, Room $room)
    {
        // Validate the incoming request data
        $request->validate([
            'number' => 'required|string|max:255', // Room number is required
            'status' => 'required|in:vacant,occupied,maintenance', // Status must be one of the three options
            'department_id' => 'required|exists:departments,id', // Department must exist in the departments table
        ]);

        // Update the room with the validated data
        $room->update($request->only('number', 'status', 'department_id'));

        // Return the updated room data
        return response()->json($room);
    }

    // Remove the specified room from the database
    public function destroy(Room $room)
    {
        // Delete the room
        $room->delete();

        // Return a 204 status code indicating successful deletion with no content
        return response()->json(null, 204);
    }
}