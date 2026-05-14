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
            [
                'name' => 'United Kingdom',
                'mobile_code' => '+44',
                'country_code' => 'GB',
                'is_whitelisted' => 1,
                'status' => 1,
                'slug' => 'united-kingdom',
            ],
            [
                'name' => 'Isle of Man',
                'mobile_code' => '+44',
                'country_code' => 'IM',
                'is_whitelisted' => 1,
                'status' => 1,
                'slug' => 'isle-of-man',
            ],
            [
                'name' => 'Jersey',
                'mobile_code' => '+44',
                'country_code' => 'JE',
                'is_whitelisted' => 1,
                'status' => 1,
                'slug' => 'jersey',
            ],
            [
                'name' => 'Guernsey',
                'mobile_code' => '+44',
                'country_code' => 'GG',
                'is_whitelisted' => 1,
                'status' => 1,
                'slug' => 'guernsey',
            ],
            [
                'name' => 'Gibraltar',
                'mobile_code' => '+350',
                'country_code' => 'GI',
                'is_whitelisted' => 1,
                'status' => 1,
                'slug' => 'gibraltar',
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
