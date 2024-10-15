<?php

namespace Modules\Service\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Service\App\Models\Service;

class ServiceController extends Controller
{
    public function index()
    {
        return Service::with('department')->get();
    }

    public function show($id)
    {
        $service = Service::with('department')->findOrFail($id);
        return response()->json($service);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'special_instructions' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $service = Service::create($request->only('name', 'type', 'description', 'special_instructions', 'department_id'));

        return response()->json($service, 201);
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'required|string',
            'special_instructions' => 'required|string',
            'department_id' => 'required|exists:departments,id',
        ]);

        $service->update($request->only('name', 'type', 'description', 'special_instructions', 'department_id'));

        return response()->json($service);
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return response()->json(null, 204);
    }
}
