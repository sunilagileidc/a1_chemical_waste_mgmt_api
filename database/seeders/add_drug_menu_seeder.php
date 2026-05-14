<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_drug_menu_seeder extends Seeder
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
                'title' => 'Drugs',
                'href' => 'drug',
                'parent_id' => $parentid,
                'seq' => 10,
                'icon' => '',
                'slug' => 'drugs',
            ],
        ]);
    }
}
