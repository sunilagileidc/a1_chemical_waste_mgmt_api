<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_wholesaler_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentid = DB::table('menus')
            ->select('id')
            ->where('title', '=', 'Configuration')
            ->value('id');

        DB::table('menus')->insert([
            [
                'title' => 'Wholesalers',
                'href' => 'wholesalers',
                'parent_id' => $parentid,
                'seq' => 11,
                'icon' => '',
                'slug' => 'wholesalers',
            ],
        ]);
    }
}
