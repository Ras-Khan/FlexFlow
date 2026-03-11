<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\WorkerAvailability;

class WorkerAvailabilityController extends Controller
{
    public function index(Request $request)
    {
        $query = WorkerAvailability::query();

        if ($request->has('worker_id')) {
            $query->where('worker_id', $request->input('worker_id'));
        }

        if ($request->has('start_date')) {
            $query->where('date', '>=', $request->input('start_date'));
        }
        if ($request->has('end_date')) {
            $query->where('date', '<=', $request->input('end_date'));
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:users,id',
            'date' => 'required|date',
            'available' => 'required|boolean',
        ]);

        $availability = WorkerAvailability::updateOrCreate(
            ['worker_id' => $validated['worker_id'], 'date' => $validated['date']],
            ['available' => $validated['available']]
        );

        return response()->json($availability, 201);
    }

    public function show(WorkerAvailability $workerAvailability)
    {
        return $workerAvailability;
    }

    public function update(Request $request, WorkerAvailability $workerAvailability)
    {
        $validated = $request->validate([
            'available' => 'sometimes|boolean',
        ]);

        $workerAvailability->update($validated);
        return $workerAvailability;
    }

    public function destroy(WorkerAvailability $workerAvailability)
    {
        $workerAvailability->delete();
        return response()->json(null, 204);
    }
}
