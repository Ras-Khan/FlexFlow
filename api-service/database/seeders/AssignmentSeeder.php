<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Assignment;
use App\Models\Job;
use App\Models\User;

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $job = Job::first();
        $worker = User::where('role', 'worker')->first();
        if (! $job || ! $worker) {
            return;
        }

        Assignment::updateOrCreate(
            ['job_id' => $job->id, 'worker_id' => $worker->id, 'start_date' => now()->toDateString()],
            ['end_date' => null]
        );
    }
}
