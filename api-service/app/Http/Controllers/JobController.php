<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index(Request $request) {
        $query = \App\Models\Job::query();
        $user = $request->user();

        // if not authenticated we should never hit this route now, but be defensive
        if ($user && $user->role === 'client') {
            $query->where('client_id', $user->id);
        }

        return $query->get();
    }

    public function show(\App\Models\Job $job) {
        return response()->json($job);
    }

    public function store(Request $request) {
        $job = \App\Models\Job::create($request->all());
        return response()->json($job, 201);
    }

    public function update(Request $request, \App\Models\Job $job) {
        $job->update($request->all());
        return response()->json($job);
    }

    public function destroy(\App\Models\Job $job) {
        $job->delete();
        return response()->json(null, 204);
    }
}
