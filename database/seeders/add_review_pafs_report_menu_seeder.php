<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_review_pafs_report_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title' => 'Review PAFs',
                'href' => 'review_paf',
                'parent_id' => 0,
                'seq' => 6,
                'icon' => 'mdi mdi-table-headers-eye',
                'slug' => 'review-pafs',
            ],

        ]);
    }
}
