<?php

namespace Database\Seeders;

use App\Models\DonorCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class DonorCategorySeeder extends Seeder
{
    
    public function run(): void
    {
        $data = [
            'GLOBAL - PRIOROTAS 4'      => 10000000,
            'GLOBAL - PRIOROTAS 3'      => 20000000,
            'GLOBAL - PRIOROTAS 2'      => 30000000,
            'GLOBAL - PRIOROTAS 1'      => 40000000
        ];

        foreach($data as $key => $value){
            DonorCategory::create([
                'category_name' =>  $key,
                'rules_nominal' => $value
            ]);
        }
    }
}
