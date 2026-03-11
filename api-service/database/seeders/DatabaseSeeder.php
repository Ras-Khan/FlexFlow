<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test2@example.com',
        ]);

        $this->call(\Database\Seeders\AdminUserSeeder::class);
        $this->call(\Database\Seeders\CompanySeeder::class);
        $this->call(\Database\Seeders\SkillSeeder::class);
        $this->call(\Database\Seeders\WorkerAvailabilitySeeder::class);
        $this->call(\Database\Seeders\AssignmentSeeder::class);
    }
}
