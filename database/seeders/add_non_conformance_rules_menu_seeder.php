<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_non_conformance_rules_menu_seeder extends Seeder
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
                'title' => 'Non-Conformace Rules',
                'href' => 'non-conformace-rules',
                'parent_id' => $parentid,
                'seq' => 12,
                'icon' => '',
                'slug' => 'non-conformace-rules',
            ],
        ]);
    }
}
