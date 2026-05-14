<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_indications_menu_seeder extends Seeder
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
                'title'     => 'Indications',
                'href'      => 'indications',
                'parent_id' => $parentid,
                'seq'       => 11,
                'icon'      => '',
                'slug'      => 'indications',
            ],
            [
                'title'     => 'Marketing Holders',
                'href'      => 'marketing_holders',
                'parent_id' => $parentid,
                'seq'       => 12,
                'icon'      => '',
                'slug'      => 'marketing-holders',
            ],
        ]);
    }
}
