<?php

namespace Database\Seeders;

use App\Models\fundType;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FundTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       
        $data = [
            "CSR",
            "Dana Kemanusiaan",
            "Dana Non Halal",
            "Entitas",
            "Green Kurban",
            "Green Kurban Mandiri",
            "Hasil Wakaf",
            "Infak",
            "Infak THK",
            "Kurban WIF",
            "non-zis",
            "Sebar Nilai",
            "Sponsorship",
            "Tabungan Kurban",
            "Tebar Hewan Kurban",
            "Wakaf",
            "Wakaf Pengembalian Investasi",
            "Zakat"
        ];

        foreach($data as $key){
            fundType::create([
                'fund_type_name' =>  $key,
            ]);
        }

    }
}
