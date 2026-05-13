<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_location_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        /*
        |--------------------------------------------------------------------------
        | Country
        |--------------------------------------------------------------------------
         */

        DB::table('countries')->insert([
            [
                'name' => 'India',
                'mobile_code' => '+91',
                'country_code' => 'IN',
                'is_whitelisted' => 1,
                'status' => 1,
                'slug' => 'india',
            ],
        ]);

        // ✅ correct way
        $country_id = DB::table('countries')
            ->where('name', 'India')
            ->value('id');

        /*
        |--------------------------------------------------------------------------
        | State
        |--------------------------------------------------------------------------
         */

        DB::table('states')->insert([
            [
                'name' => 'Karnataka',
                'status' => 1,
                'country_id' => $country_id,
                'slug' => 'karnataka',
            ],
        ]);

        $state_id = DB::table('states')
            ->where('name', 'Karnataka')
            ->value('id');

        /*
        |--------------------------------------------------------------------------
        | City
        |--------------------------------------------------------------------------
         */

        DB::table('cities')->insert([
            [
                'name' => 'Bengaluru',
                'status' => 1,
                'country_id' => $country_id,
                'state_id' => $state_id,
                'slug' => 'bengaluru',
            ],
        ]);

    }
}
