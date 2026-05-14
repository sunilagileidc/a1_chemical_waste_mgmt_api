<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_option_action_categories_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Action Cat
        $ac_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'ACTION_CATEGORIES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'PAF',
                'longname' => 'PAF',
                'seq' => 6,
                'parent_id' => $ac_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'ac_paf',
            ],
        ]);
    }
}
