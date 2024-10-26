<?php

namespace Modules\PatientsAdmin\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PatientsAdmin\App\Models\PatientVisit;
use Illuminate\Support\Facades\Auth;

class PatientVisitController extends Controller
{
    
    public function index()
    {
        return PatientVisit::with('patient')->get();
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'patient_national_id' => 'required|exists:patients,national_id',
            'entry_time' => 'required|date',
            'exit_time' => 'nullable|date|after:entry_time',
        ]);

        $visit = PatientVisit::create($request->only('patient_national_id', 'entry_time', 'exit_time'));

        return response()->json($visit, 201);
    }

  
    public function update(Request $request, PatientVisit $visit)
    {
        $request->validate([
            'entry_time' => 'required|date',
            'exit_time' => 'nullable|date|after:entry_time',
        ]);

        $visit->update($request->only('entry_time', 'exit_time'));

        return response()->json($visit);
    }

  
    public function destroy(PatientVisit $visit)
    {
        $visit->delete();

        return response()->json(null, 204);
    }
}
