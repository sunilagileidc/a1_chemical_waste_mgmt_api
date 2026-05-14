<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_menu_user_dashboard_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title' => 'User Dashboard',
                'href' => 'user_dashboard',
                'parent_id' => 0,
                'seq' => 11,
                'icon' => 'mdi mdi-view-dashboard',
                'slug' => 'user_dashboard',
            ],
        ]);
    }
}
