<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use App\Models\Job;
use Illuminate\Http\Request;

class SkillController extends Controller
{
    public function index()
    {
        return Skill::all();
    }

    public function show(Skill $skill)
    {
        return $skill;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:skills',
        ]);

        $skill = Skill::create($validated);
        return response()->json($skill, 201);
    }

    public function update(Request $request, Skill $skill)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:skills,name,' . $skill->id,
        ]);

        $skill->update($validated);
        return $skill;
    }

    public function destroy(Skill $skill)
    {
        $skill->delete();
        return response()->json(null, 204);
    }

    public function attachToUser(Request $request, User $user)
    {
        $data = $request->validate([
            'skill_id' => 'required|exists:skills,id',
        ]);

        $user->skills()->syncWithoutDetaching($data['skill_id']);
        return response()->json($user->skills);
    }

    public function detachFromUser(User $user, Skill $skill)
    {
        $user->skills()->detach($skill);
        return response()->json(null, 204);
    }

    public function attachToJob(Request $request, Job $job)
    {
        $data = $request->validate([
            'skill_id' => 'required|exists:skills,id',
        ]);

        $job->skills()->syncWithoutDetaching($data['skill_id']);
        return response()->json($job->skills);
    }

    public function detachFromJob(Job $job, Skill $skill)
    {
        $job->skills()->detach($skill);
        return response()->json(null, 204);
    }
}
