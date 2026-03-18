<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $query = Payroll::with(['worker', 'paystubs']);
        $user = $request->user();

        if ($user && $user->role === 'worker') {
            $query->where('worker_id', $user->id);
        }

        return $query->get();
    }

    public function show(Payroll $payroll)
    {
        return response()->json($payroll->load(['worker', 'paystubs']));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:users,id',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after:period_start',
            'gross_amount' => 'required|numeric|min:0',
            'net_amount' => 'required|numeric|min:0',
            'taxes' => 'required|numeric|min:0',
            'status' => 'required|in:pending,processed,paid',
        ]);

        $payroll = Payroll::create($validated);
        return response()->json($payroll, 201);
    }

    public function update(Request $request, Payroll $payroll)
    {
        $validated = $request->validate([
            'worker_id' => 'sometimes|required|exists:users,id',
            'period_start' => 'sometimes|required|date',
            'period_end' => 'sometimes|required|date|after:period_start',
            'gross_amount' => 'sometimes|required|numeric|min:0',
            'net_amount' => 'sometimes|required|numeric|min:0',
            'taxes' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:pending,processed,paid',
        ]);

        $payroll->update($validated);
        return response()->json($payroll);
    }

    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return response()->json(null, 204);
    }
}
