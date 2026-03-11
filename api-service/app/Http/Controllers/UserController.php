<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index() {
        return \App\Models\User::all();
    }

    public function show(\App\Models\User $user) {
        return response()->json($user);
    }

    public function store(Request $request) {
        $user = \App\Models\User::create($request->all());
        return response()->json($user, 201);
    }

    public function update(Request $request, \App\Models\User $user) {
        $user->update($request->all());
        return response()->json($user);
    }

    public function destroy(\App\Models\User $user) {
        $user->delete();
        return response()->json(null, 204);
    }
}