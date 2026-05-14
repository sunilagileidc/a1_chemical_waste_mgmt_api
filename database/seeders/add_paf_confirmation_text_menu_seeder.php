<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_confirmation_text_menu_seeder extends Seeder
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
                'title' => 'Confirmation Texts',
                'href' => 'confirmation-text',
                'parent_id' => $parentid,
                'seq' => 13,
                'icon' => '',
                'slug' => 'confirmation-text',
            ],
        ]);
    }
}
