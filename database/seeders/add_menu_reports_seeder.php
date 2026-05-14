<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_menu_reports_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Reports',
                'href'      => '#',
                'parent_id' => 0,
                'seq'       => 4,
                'icon'      => 'mdi mdi-chart-box-multiple',
                'slug'      => 'reports',
            ],
        ]);

        $parentid = DB::table('menus')
            ->select('id')
            ->where('title', '=', 'Reports')
            ->value('id');

        DB::table('menus')->insert([
            [
                'title'     => 'Off-Label PAFs',
                'href'      => 'off_label_pafs',
                'parent_id' => $parentid,
                'seq'       => 1,
                'icon'      => '',
                'slug'      => 'off-label-paf',
            ],
        ]);
    }
}
