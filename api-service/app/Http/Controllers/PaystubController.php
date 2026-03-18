<?php

namespace App\Http\Controllers;

use App\Models\Paystub;
use Illuminate\Http\Request;

class PaystubController extends Controller
{
    public function index(Request $request)
    {
        $query = Paystub::with('payroll');
        $user = $request->user();

        if ($user && $user->role === 'worker') {
            $query->whereHas('payroll', function ($q) use ($user) {
                $q->where('worker_id', $user->id);
            });
        }

        return $query->get();
    }

    public function show(Paystub $paystub)
    {
        return response()->json($paystub->load('payroll'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payroll_id' => 'required|exists:payrolls,id',
            'file_path' => 'required|string',
        ]);

        $paystub = Paystub::create($validated);
        return response()->json($paystub, 201);
    }

    public function update(Request $request, Paystub $paystub)
    {
        $validated = $request->validate([
            'payroll_id' => 'sometimes|required|exists:payrolls,id',
            'file_path' => 'sometimes|required|string',
        ]);

        $paystub->update($validated);
        return response()->json($paystub);
    }

    public function destroy(Paystub $paystub)
    {
        $paystub->delete();
        return response()->json(null, 204);
    }
}
