<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class JobController extends Controller
{
    public function index() {
        return \App\Models\Job::all();
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
