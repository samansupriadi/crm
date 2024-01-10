<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Symfony\Component\Uid\Ulid;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run():void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('users')->truncate();
        
        User::create([
            'id'        => 100,
            'name'      => 'Saman Supriadi',
            'email'     => 'saman@sinergifoundation.org',
            'password'  => Hash::make('rahasia123@')
        ]);
        
        User::create([
            'id'        => 101,
            'name'      => 'Didiet',
            'email'     => 'didieto@sinergifoundation.org',
            'password'  => Hash::make('rahasia123@')
        ]);

        User::create([
          'id'        => 2,
          'name'      => 'Tim Funding',
          'email'     => 'dev@sinergifoundation.org',
          'password'  => Hash::make('rahasia123@')
        ]);

        User::create([
          'id'        => 3,
          'name'      => 'Marketing Group',
          'email'     => 'dev@sinergifoundation.org',
          'password'  => Hash::make('rahasia123@')
        ]);
        
        User::create([
          'id'        => 4,
          'name'      => 'Support Group',
          'email'     => 'dev@sinergifoundation.org',
          'password'  => Hash::make('rahasia123@')
        ]);

        User::create([
          'id'        => 70,
          'name'      => 'Test Grup',
          'email'     => 'dev@sinergifoundation.org',
          'password'  => Hash::make('rahasia123@')
        ]);
    
        $jsonUserData = '[
            {
              "id": "1",
              "user_name": "admin",
              "first_name": "",
              "last_name": "Administrator",
              "department": "",
              "email1": "dev@sinergifoundation.org"
            },
            {
              "id": "5",
              "user_name": "fundingoffline1",
              "first_name": "",
              "last_name": "Istiana Mita",
              "department": "",
              "email1": "funding.offline1@sinergi.id"
            },
            {
              "id": "8",
              "user_name": "ceo",
              "first_name": "",
              "last_name": "Mr CEO",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "9",
              "user_name": "fundingoffline3",
              "first_name": "",
              "last_name": "Nana",
              "department": "",
              "email1": "funding.offline3@sinergi.id"
            },
            {
              "id": "10",
              "user_name": "fundingoffline4",
              "first_name": "",
              "last_name": "Usep Muad",
              "department": "Offline Funding",
              "email1": "funding.offline4@sinergi.id"
            },
            {
              "id": "11",
              "user_name": "salesman5",
              "first_name": "",
              "last_name": "Funding Offline 5",
              "department": "",
              "email1": "funding.offline5@sinergi.id"
            },
            {
              "id": "12",
              "user_name": "salesman6",
              "first_name": "",
              "last_name": "Salesman 6",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "13",
              "user_name": "salesman7",
              "first_name": "",
              "last_name": "Salesman 7",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "14",
              "user_name": "salesman8",
              "first_name": "",
              "last_name": "Salesman 8",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "15",
              "user_name": "salesman9",
              "first_name": "",
              "last_name": "Salesman 9",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "16",
              "user_name": "salesman10",
              "first_name": "",
              "last_name": "Salesman 10",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "17",
              "user_name": "salesman11",
              "first_name": "",
              "last_name": "Salesman 11",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "18",
              "user_name": "salesman12",
              "first_name": "",
              "last_name": "Salesman 12",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "19",
              "user_name": "salesman13",
              "first_name": "",
              "last_name": "Salesman 13",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "26",
              "user_name": "salesman20",
              "first_name": "",
              "last_name": "Salesman 20",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "29",
              "user_name": "dataproses",
              "first_name": "",
              "last_name": "Eri Pitria Nursolehah",
              "department": "",
              "email1": "funding.support@sinergi.id"
            },
            {
              "id": "30",
              "user_name": "techsupport1",
              "first_name": "",
              "last_name": "Technical Support 1",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "31",
              "user_name": "techsupport2",
              "first_name": "",
              "last_name": "Technical Support 2",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "38",
              "user_name": "techsupport9",
              "first_name": "",
              "last_name": "Technical Support 9",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "40",
              "user_name": "demo.jababeka",
              "first_name": "",
              "last_name": "Demo Jababeka",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "41",
              "user_name": "demo.pp",
              "first_name": "",
              "last_name": "Demo PP Properti",
              "department": "",
              "email1": "crm.kazee@gmail.com"
            },
            {
              "id": "44",
              "user_name": "finance",
              "first_name": "",
              "last_name": "Finance Manager",
              "department": "",
              "email1": "fin@mil.com"
            },
            {
              "id": "45",
              "user_name": "teller",
              "first_name": "",
              "last_name": "Laras",
              "department": "",
              "email1": "teller@sada.com"
            },
            {
              "id": "46",
              "user_name": "contactcenter",
              "first_name": "",
              "last_name": "Ochy",
              "department": "",
              "email1": "cc@sinergi.id"
            },
            {
              "id": "47",
              "user_name": "strategic_fundingmarketing",
              "first_name": "Strategic",
              "last_name": "Funding & Marketing",
              "department": "",
              "email1": "strategicfm@sinergi.id"
            },
            {
              "id": "48",
              "user_name": "Funding Manager",
              "first_name": "",
              "last_name": "Sudisto",
              "department": "",
              "email1": "fundingmanager@sinergi.id"
            },
            {
              "id": "49",
              "user_name": "customer_relation",
              "first_name": "",
              "last_name": "Customer Relation",
              "department": "",
              "email1": "cr@sinergi.id"
            },
            {
              "id": "50",
              "user_name": "bankaccount",
              "first_name": "",
              "last_name": "Bank Account",
              "department": "",
              "email1": "bankaccount@sinergi.id"
            },
            {
              "id": "51",
              "user_name": "ceo_sf",
              "first_name": "",
              "last_name": "Asep Irawan",
              "department": "",
              "email1": "asep.irawan@sinergi.id"
            },
            {
              "id": "52",
              "user_name": "fundingoffline2",
              "first_name": "",
              "last_name": "Dini Wahdini",
              "department": "",
              "email1": "fundingoffline2@sinergi.id"
            },
            {
              "id": "53",
              "user_name": "fundingoffline5",
              "first_name": "fun5",
              "last_name": "crm wakaf",
              "department": "",
              "email1": "wahyu@sinergifoundation.org"
            },
            {
              "id": "54",
              "user_name": "fundingoffline6",
              "first_name": "",
              "last_name": "Yadi Mulyadi",
              "department": "",
              "email1": "fundingoffline6@sinergi.id"
            },
            {
              "id": "55",
              "user_name": "fundingonline1",
              "first_name": "",
              "last_name": "Halimah SAM",
              "department": "",
              "email1": "fundingonline1@sinergi.id"
            },
            {
              "id": "56",
              "user_name": "fundingonline2",
              "first_name": "",
              "last_name": "Annisa",
              "department": "",
              "email1": "annisa@sinergifoundation.org"
            },
            {
              "id": "57",
              "user_name": "SPV_CRM",
              "first_name": "",
              "last_name": "Wahyu irawan",
              "department": "",
              "email1": "masterdata@sinergifoundation.org"
            },
            {
              "id": "58",
              "user_name": "kasir",
              "first_name": "",
              "last_name": "Finance",
              "department": "",
              "email1": "kasir@sinergifoundation.org"
            },
            {
              "id": "59",
              "user_name": "temp.admin",
              "first_name": "",
              "last_name": "Temporary Admin",
              "department": "",
              "email1": "temp@admin.com"
            },
            {
              "id": "60",
              "user_name": "piket.paskal",
              "first_name": "",
              "last_name": "Piket Paskal",
              "department": "",
              "email1": "piket1@sinergi.com"
            },
            {
              "id": "61",
              "user_name": "rbc.holis",
              "first_name": "RBC",
              "last_name": "HOLIS",
              "department": "",
              "email1": "rbc.holis@sinergi.com"
            },
            {
              "id": "62",
              "user_name": "rbc.katapang",
              "first_name": "",
              "last_name": "Piket RBC Katapang",
              "department": "",
              "email1": "rbc.katapang@sinergi.com"
            },
            {
              "id": "63",
              "user_name": "Fundingirc",
              "first_name": "",
              "last_name": "Noviyatin",
              "department": "",
              "email1": "noviyatin@sinergifoundation.org"
            },
            {
              "id": "64",
              "user_name": "Admin Wakaf",
              "first_name": "",
              "last_name": "Vivi",
              "department": "",
              "email1": "andeamalinda@yahoo.com"
            },
            {
              "id": "65",
              "user_name": "FM Wakaf",
              "first_name": "Eren",
              "last_name": "Pramula",
              "department": "",
              "email1": "erenpramula@gmail.com"
            },
            {
              "id": "66",
              "user_name": "Funding Wakaf",
              "first_name": "",
              "last_name": "Nurodin",
              "department": "",
              "email1": "nurodin@sinergifoundation.org"
            },
            {
              "id": "67",
              "user_name": "arifnuryadi",
              "first_name": "Arif",
              "last_name": "Nuryadi",
              "department": "",
              "email1": "arifnuryadi@sinergifoundation.org"
            },
            {
              "id": "68",
              "user_name": "test.contact.center",
              "first_name": "",
              "last_name": "ajaskdjasljd",
              "department": "",
              "email1": "tes@gte.com"
            },
            {
              "id": "69",
              "user_name": "test.role",
              "first_name": "",
              "last_name": "test",
              "department": "",
              "email1": "tesa@lkajsd.com"
            },
            {
              "id": "71",
              "user_name": "Admin IT",
              "first_name": "IT",
              "last_name": "IT",
              "department": "",
              "email1": "admin@gmail.com"
            },
            {
              "id": "72",
              "user_name": "Admin DSB",
              "first_name": "",
              "last_name": "Ella M",
              "department": "",
              "email1": "admin@dsb.com"
            },
            {
              "id": "73",
              "user_name": "Fatih",
              "first_name": "Fatih",
              "last_name": "Fatih",
              "department": "",
              "email1": "fatih@admin.com"
            },
            {
              "id": "74",
              "user_name": "Input Online",
              "first_name": "",
              "last_name": "Input Online",
              "department": "",
              "email1": "inputonline@gmail.com"
            },
            {
              "id": "75",
              "user_name": "Funding Consultant",
              "first_name": "",
              "last_name": "Dea Sunarwan",
              "department": "",
              "email1": "fundingconsultant@gmail.com"
            },
            {
              "id": "76",
              "user_name": "samsul",
              "first_name": "samsul",
              "last_name": "samsul",
              "department": "",
              "email1": "samsul@admin.com"
            },
            {
              "id": "77",
              "user_name": "rahman",
              "first_name": "Rahman",
              "last_name": "Fauzi",
              "department": "",
              "email1": "fauzirahman@sinergifoundation.org"
            },
            {
              "id": "78",
              "user_name": "Teller_wakaf",
              "first_name": "",
              "last_name": "Halimah",
              "department": "",
              "email1": "tellerwakaf@gmail.com"
            },
            {
              "id": "79",
              "user_name": "Admin_Bisnis",
              "first_name": "Ira N",
              "last_name": "Ira N",
              "department": "",
              "email1": "bisnis@sinergifoundation.org"
            },
            {
              "id": "80",
              "user_name": "Noviatin",
              "first_name": "novi",
              "last_name": "Noviatin",
              "department": "",
              "email1": "noviatin@gmail.com"
            },
            {
              "id": "81",
              "user_name": "Accounting",
              "first_name": "Rosita",
              "last_name": "Dewi Hayati",
              "department": "",
              "email1": "acounting@gmal.com"
            },
            {
              "id": "82",
              "user_name": "BISNIS",
              "first_name": "EREN PRAMULA",
              "last_name": "EREN PRAMULA",
              "department": "",
              "email1": "eren@sinergifoundation.org"
            },
            {
              "id": "83",
              "user_name": "Digital Fundraising",
              "first_name": "Aini",
              "last_name": "Aini N",
              "department": "",
              "email1": "df@sinergifoundation.org"
            },
            {
              "id": "84",
              "user_name": "fopaskal",
              "first_name": "",
              "last_name": "Laras",
              "department": "",
              "email1": "annisa@sinergifoundation.org"
            },
            {
              "id": "85",
              "user_name": "terry",
              "first_name": "terry",
              "last_name": "terry",
              "department": "",
              "email1": "terry@sinergifoundation.org"
            },
            {
              "id": "86",
              "user_name": "ramdan",
              "first_name": "Muhammad",
              "last_name": "Ramdan",
              "department": "",
              "email1": "ramdan@sinergifoundation.org"
            },
            {
              "id": "87",
              "user_name": "sftangerang",
              "first_name": "SF",
              "last_name": "Tangerang",
              "department": "",
              "email1": "sftagerang@sinergifoundation.org"
            }
          ]';
        $userData = json_decode($jsonUserData, true);
        foreach($userData as $item){
            User::insert([
                'ulid'      => Ulid::generate(),
                'id'        => $item['id'],
                'name'      => $item['first_name'] . ' ' . $item['last_name'],
                'email'     => $item['email1'],
                'password'  => Hash::make('rahasia123@')
            ]);
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');  
    }
}
