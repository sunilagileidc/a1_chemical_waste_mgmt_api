<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_countries_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('countries')->insert([
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
    }
}
