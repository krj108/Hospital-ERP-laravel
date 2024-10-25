<?php

namespace Modules\Rooms\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Rooms\App\Models\Room;
use Illuminate\Support\Facades\Auth;

class RoomController extends Controller
{
    // Display a listing of all rooms with their associated departments
    public function index()
    {
        return Room::with('department')->get();
    }

    // Store a newly created room in the database (Admin Only)
    public function store(Request $request)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate and create the new room
        $request->validate([
            'number' => 'required|string|max:255',
            'status' => 'required|in:vacant,occupied,maintenance',
            'department_id' => 'required|exists:departments,id',
        ]);

        $room = Room::create($request->only('number', 'status', 'department_id'));

        return response()->json($room, 201);
    }

    // Update the specified room in the database (Admin and Doctor)
    public function update(Request $request, Room $room)
    {
        if (!Auth::user()->hasRole('admin') && !Auth::user()->hasRole('doctor')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Validate and update the room
        $request->validate([
            'number' => 'required|string|max:255',
            'status' => 'required|in:vacant,occupied,maintenance',
            'department_id' => 'required|exists:departments,id',
        ]);

        $room->update($request->only('number', 'status', 'department_id'));

        return response()->json($room);
    }

    // Remove the specified room from the database (Admin Only)
    public function destroy(Room $room)
    {
        if (!Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $room->delete();

        return response()->json(null, 204);
    }
}