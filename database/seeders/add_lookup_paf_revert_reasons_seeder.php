<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_lookup_paf_revert_reasons_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'PAF_REVERT_REASON',
                'longname' => 'PAF_REVERT_REASON',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'paf-revert-reason',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'PAF_REVERT_REASON')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'PAF marked for rejection',
                'longname' => 'PAF marked for rejection',
                'seq' => 1,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-paf-marked-for-rejection',
            ],
            [
                'shortname' => 'Incorrect patient',
                'longname' => 'Incorrect patient',
                'seq' => 2,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-incorrect-patient',
            ],
            [
                'shortname' => 'Supply issues',
                'longname' => 'Supply issues',
                'seq' => 3,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-supply-issues',
            ],
            [
                'shortname' => 'Administrative errors',
                'longname' => 'Administrative errors',
                'seq' => 4,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-administrative-errors',
            ],
            [
                'shortname' => 'Wrong dispensary',
                'longname' => 'Wrong dispensary',
                'seq' => 5,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-wrongdispensary',
            ],
            [
                'shortname' => 'Out of stock',
                'longname' => 'Out of stock',
                'seq' => 6,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-outofstock',
            ],
            [
                'shortname' => 'Other (please state)',
                'longname' => 'Other (please state)',
                'seq' => 10,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'revert-other',
            ],
        ]);
    }
}