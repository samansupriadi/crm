<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\EntitySeeder;
use Database\Seeders\ProgramSeeder;
use Database\Seeders\DonorCategorySeeder;
use Database\Seeders\accountPaymentSeeder;
use Database\Seeders\ProgramCategorySeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            DonorCategorySeeder::class,
            ProgramCategorySeeder::class,
            ProgramSeeder::class,
            FundTypeSeeder::class,
            PaymentMethodSeeder::class,
            accountPaymentSeeder::class,
            DivisionSeeder::class,
            EntitySeeder::class

        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
