<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_rejection_reason_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'PAF_REJECTION_REASON',
                'longname' => 'PAF_REJECTION_REASON',
                'seq' => 11,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'paf-rejection-reason',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->where('shortname', 'PAF_REJECTION_REASON')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Inconsistent information between PAF and prescription',
                'longname' => 'Inconsistent information between PAF and prescription',
                'seq' => 1,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'inconsistent-information-between-PAF-and-prescription',
            ],
            [
                'shortname' => 'Supply issues',
                'longname' => 'Supply issues',
                'seq' => 2,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'supply-issues',
            ],
            [
                'shortname' => 'Incorrect PPP (different brand or clinical trials) ',
                'longname' => 'Incorrect PPP (different brand or clinical trials) ',
                'seq' => 3,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'incorrect-PPP',
            ],
            [
                'shortname' => 'Requested by the prescriber for no safety reasons',
                'longname' => 'Requested by the prescriber for no safety reasons',
                'seq' => 4,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'requested-by-the-prescriber-for-no-safety-reasons',
            ],
            [
                'shortname' => 'Administrative Errors',
                'longname' => 'Administrative Errors',
                'seq' => 5,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'administrative-errors',
            ],
            [
                'shortname' => 'PAF overdue by more than 7 days',
                'longname' => 'PAF overdue by more than 7 days',
                'seq' => 6,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'PAF-overdue-by-more-than-7-days',
            ],
            [
                'shortname' => 'Change in required data (e.g. strength, quantity)',
                'longname' => 'Change in required data (e.g. strength, quantity)',
                'seq' => 7,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'change-in-required-data',
            ],
            [
                'shortname' => 'Incorrect Patient',
                'longname' => 'Incorrect Patient',
                'seq' => 8,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'incorrect-patient',
            ],
            [
                'shortname' => 'Duplication of Form',
                'longname' => 'Duplication of Form',
                'seq' => 9,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'duplication-of-form',
            ]
            
        ]);
    }
}
