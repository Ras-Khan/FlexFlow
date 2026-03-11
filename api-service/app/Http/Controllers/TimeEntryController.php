<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimeEntryController extends Controller
{
    public function index(Request $request)
    {
        $start = $request->query('start');
        $end = $request->query('end');

        $query = TimeEntry::with('worker')->whereBetween('date', [$start, $end]);
        if (Auth::user()->role === 'worker') {
            $query->where('worker_id', Auth::id());
        }
        if (Auth::user()->role === 'client') {
            $query->whereHas('job', function ($q) {
                $q->where('client_id', Auth::id());
            });
        }

        $entries = $query->get();

        if ($request->query('format') === 'week') {
            $week = [];
            foreach ($entries as $entry) {
                $workerId = $entry->worker_id;
                if (!isset($week[$workerId])) {
                    $week[$workerId] = [
                        'worker_id' => $workerId,
                        'worker_name' => $entry->worker->name,
                        'dates' => [],
                        'hours' => []
                    ];
                }
                $day = date('D', strtotime($entry->date));
                $week[$workerId]['dates'][$day] = $entry->date;
                $week[$workerId]['hours'][$day] = $entry->hours;
            }
            return array_values($week);
        }

        return $entries;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'worker_id' => 'required|exists:users,id',
            'job_id' => 'required|exists:jobs,id',
            'date' => 'required|date',
            'hours' => 'required|numeric',
        ]);

        return TimeEntry::updateOrCreate(
            ['worker_id' => $data['worker_id'], 'job_id' => $data['job_id'], 'date' => $data['date']],
            ['hours' => $data['hours']]
        );
    }

    public function approve($id)
    {
        $entry = TimeEntry::findOrFail($id);
        $entry->status = 'approved';
        $entry->save();
        return $entry;
    }
}
