<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_settings_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Settings',
                'href'      => '#',
                'parent_id' => 0,
                'seq'       => 2,
                'icon'      => 'mdi mdi-cog',
                'slug'      => 'settings',
            ],
        ]);
    }
}
