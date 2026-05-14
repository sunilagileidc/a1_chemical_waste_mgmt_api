<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_paf_review_reason_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'PAF_REVIEW_REASON',
                'longname' => 'PAF_REVIEW_REASON',
                'seq' => 11,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'paf-review-reason',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->where('shortname', 'PAF_REVIEW_REASON')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Duplication of Form',
                'longname' => 'Duplication of Form',
                'seq' => 1,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'duplication-of-form',
            ],
            [
                'shortname' => 'Incorrect Patient',
                'longname' => 'Incorrect Patient',
                'seq' => 2,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'incorrect-patient',
            ],
            [
                'shortname' => 'Change in required data (e.g. strength, quantity)',
                'longname' => 'Change in required data (e.g. strength, quantity)',
                'seq' => 3,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'change-in-required-data',
            ],
            [
                'shortname' => 'PAF overdue by more than 7 days',
                'longname' => 'PAF overdue by more than 7 days',
                'seq' => 4,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'PAF-overdue-by-more-than-7-days',
            ],
        ]);
    }
}
