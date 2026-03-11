<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkerAvailability;
use App\Models\User;

class WorkerAvailabilitySeeder extends Seeder
{
    public function run(): void
    {
        $worker = User::where('role', 'worker')->first();
        if (! $worker) {
            return;
        }

        $today = now()->startOfDay();
        for ($i = 0; $i < 7; $i++) {
            WorkerAvailability::updateOrCreate(
                ['worker_id' => $worker->id, 'date' => $today->copy()->addDays($i)->toDateString()],
                ['available' => true]
            );
        }
    }
}
