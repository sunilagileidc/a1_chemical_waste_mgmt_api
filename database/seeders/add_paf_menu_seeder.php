<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title' => 'PAFs',
                'href' => 'paf',
                'parent_id' => 0,
                'seq' => 5,
                'icon' => 'mdi mdi-receipt-text-outline',
                'slug' => 'pafs',
            ],

        ]);
    }
}
