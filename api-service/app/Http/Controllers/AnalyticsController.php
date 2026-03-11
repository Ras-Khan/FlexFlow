<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Company;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $companies = Company::all();
        $labels = $companies->pluck('name');
        $values = $companies->pluck('value');

        return response()->json([
            'workers' => [
                'labels' => ['Jan', 'Feb', 'Mar'],
                'data' => [120, 135, 150]
            ],
            'clients' => [
                'labels' => $labels,
                'data' => $values
            ]
        ]);
    }
}
