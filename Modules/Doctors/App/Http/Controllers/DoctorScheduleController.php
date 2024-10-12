<?php

namespace Modules\Doctors\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Doctors\App\Models\DoctorSchedule;

class DoctorScheduleController extends Controller
{

    public function index()
    {
        return DoctorSchedule::with('doctor')->get();
    }

  
    public function store(Request $request)
    {
        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $schedule = DoctorSchedule::create($request->only('doctor_id', 'start_date', 'end_date', 'start_time', 'end_time'));

        return response()->json($schedule, 201);
    }

   
    public function update(Request $request, DoctorSchedule $schedule)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
        ]);

        $schedule->update($request->only('start_date', 'end_date', 'start_time', 'end_time'));

        return response()->json($schedule);
    }

    
    public function destroy(DoctorSchedule $schedule)
    {
        $schedule->delete();

        return response()->json(null, 204);
    }
}

