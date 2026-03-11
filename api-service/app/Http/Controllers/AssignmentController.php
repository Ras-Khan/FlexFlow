<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Assignment;

class AssignmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Assignment::query();

        if ($request->has('worker_id')) {
            $query->where('worker_id', $request->input('worker_id'));
        }
        if ($request->has('job_id')) {
            $query->where('job_id', $request->input('job_id'));
        }
        if ($request->has('active')) {
            if ($request->input('active')) {
                $query->whereNull('end_date');
            } else {
                $query->whereNotNull('end_date');
            }
        }

        return $query->get();
    }

    public function show(Assignment $assignment)
    {
        return $assignment;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'job_id' => 'required|exists:jobs,id',
            'worker_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $assignment = Assignment::create($validated);
        return response()->json($assignment, 201);
    }

    public function update(Request $request, Assignment $assignment)
    {
        $validated = $request->validate([
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $assignment->update($validated);
        return $assignment;
    }

    public function destroy(Assignment $assignment)
    {
        $assignment->delete();
        return response()->json(null, 204);
    }
}
