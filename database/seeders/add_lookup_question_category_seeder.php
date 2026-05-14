<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class add_lookup_question_category_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'POLICY_QUESTIONS_CATEGORY',
                'longname'  => 'POLICY_QUESTIONS_CATEGORY',
                'seq'       => 1,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'policy-questions-category',
            ]
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'POLICY_QUESTIONS_CATEGORY')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Drugs',
                'longname'  => 'Drugs',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'drugs-qcat',
            ]
        ]);
    }
}
