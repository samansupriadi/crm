<?php

namespace Database\Seeders;

use App\Models\Division;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DivisionSeeder extends Seeder
{

    public function run(): void
    {
        $data = [
            'ZIS-CRM', 'ZIS-FUNDING', 'NAZIR-CRM', 'NAZIR-FUNDING', 'HOLDING'
        ];

        foreach ($data as $k) {
            Division::create([
                'name' =>  $k
            ]);
        }
    }
}
