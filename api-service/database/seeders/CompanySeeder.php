<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        $companies = [
            ['name' => 'Acme', 'value' => 5000],
            ['name' => 'Globex', 'value' => 7200],
            ['name' => 'Initech', 'value' => 3100],
        ];

        foreach ($companies as $c) {
            Company::updateOrCreate(
                ['name' => $c['name']],
                ['value' => $c['value']]
            );
        }
    }
}
