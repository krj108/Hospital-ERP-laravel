<?php

namespace Modules\Doctors\App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Modules\Doctors\App\Models\Specialization;

class SpecializationController extends Controller
{
    public function index()
    {
        return Specialization::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $specialization = Specialization::create($request->only('name'));

        return response()->json($specialization, 201);
    }

    public function update(Request $request, Specialization $specialization)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $specialization->update($request->only('name'));

        return response()->json($specialization);
    }

    public function destroy(Specialization $specialization)
    {
        $specialization->delete();

        return response()->json(null, 204);
    }
}
