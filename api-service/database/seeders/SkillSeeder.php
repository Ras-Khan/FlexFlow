<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = ['Forklift', 'Welding', 'Customer Service', 'Packing'];

        foreach ($skills as $name) {
            Skill::updateOrCreate(['name' => $name]);
        }
    }
}
