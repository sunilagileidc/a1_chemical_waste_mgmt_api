<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class add_lookup_jobtitles_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'JOB_TITLES',
                'longname'  => 'JOB_TITLES',
                'seq'       => 1,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'job-titles',
            ]
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'JOB_TITLES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Officer',
                'longname'  => 'Officer',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'officer',
            ],
            [
                'shortname' => 'Support',
                'longname'  => 'Support',
                'seq'       => 3,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'support',
            ],
            [
                'shortname' => 'Technical',
                'longname'  => 'Technical',
                'seq'       => 4,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'technical',
            ],
        ]);
    }
}
