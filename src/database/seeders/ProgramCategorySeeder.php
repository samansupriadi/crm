<?php

namespace Database\Seeders;

use App\Models\ProgramCategory;
use Illuminate\Database\Seeder;
use Symfony\Component\Uid\Ulid;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProgramCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $datas = '
        [
            {"productcategoryid":"25","productcategory":"Akikah"},
            {"productcategoryid":"7","productcategory":"CSR"},
            {"productcategoryid":"8","productcategory":"Dana Kemanusiaan"},
            {"productcategoryid":"18","productcategory":"Dana Non Halal"},
            {"productcategoryid":"9","productcategory":"Entitas"},
            {"productcategoryid":"21","productcategory":"Green Kurban"},
            {"productcategoryid":"19","productcategory":"Green Kurban Mandiri"},
            {"productcategoryid":"11","productcategory":"Hasil Wakaf"},
            {"productcategoryid":"3","productcategory":"Infak"},
            {"productcategoryid":"24","productcategory":"Infak Operasional Nazhir"},
            {"productcategoryid":"12","productcategory":"Infak THK"},
            {"productcategoryid":"20","productcategory":"Kurban WIF"},
            {"productcategoryid":"22","productcategory":"non-zis"},
            {"productcategoryid":"13","productcategory":"Sebar Nilai"},
            {"productcategoryid":"23","productcategory":"Sinergi Tanggap Bencana"},
            {"productcategoryid":"14","productcategory":"Sponsorship"},
            {"productcategoryid":"6","productcategory":"Tabungan Kurban"},
            {"productcategoryid":"15","productcategory":"Tebar Hewan Kurban"},
            {"productcategoryid":"17","productcategory":"Wakaf"},
            {"productcategoryid":"16","productcategory":"Wakaf Pengembalian Investasi"},
            {"productcategoryid":"5","productcategory":"Zakat"}
        ]';
        
        $programCategoryData = json_decode($datas, true);
        foreach($programCategoryData as $key){
            ProgramCategory::insert([
                'ulid'          => Ulid::generate(),
                'id'            => $key['productcategoryid'],
                'category_name' => $key['productcategory'],
            ]);
        }
    }
}
