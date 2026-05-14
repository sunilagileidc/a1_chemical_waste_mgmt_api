<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_wholesaler_dashboard_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title' => 'Wholesaler Dashboard',
                'href' => 'wholesaler_dashboard',
                'parent_id' => 0,
                'seq' => 12,
                'icon' => 'mdi mdi-view-dashboard',
                'slug' => 'wholesaler-dashboard',
            ],
        ]);
    }
}
