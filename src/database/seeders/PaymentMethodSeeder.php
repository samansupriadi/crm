<?php

namespace Database\Seeders;

use App\Models\paymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PaymentMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            'Dijemput', 'Langsung', 'Transfer', 'Konter', 'Mobile Konter', 'Kantor Kas', 'Cek / Wesel', 'MPZ', 'WEBSITE'
        ];

        foreach($data as $item){
            paymentMethod::create([
                'payement_method'  => $item,
            ]);
        }
    }
}
