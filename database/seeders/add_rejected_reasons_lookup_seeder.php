<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_rejected_reasons_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'REJECTED_REASONS',
                'longname'  => 'REJECTED_REASONS',
                'seq'       => 11,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'rejected-reasons',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->where('shortname', 'REJECTED_REASONS')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Incomplete Registration Details',
                'longname'  => 'Incomplete Registration Details',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'incomplete-registration-details',
            ],
            [
                'shortname' => 'Invalid or Unverified Documents',
                'longname'  => 'Invalid or Unverified Documents',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'invalid-documents',
            ],
            [
                'shortname' => 'Duplicate Account Detected',
                'longname'  => 'Duplicate Account Detected',
                'seq'       => 3,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'duplicate-account',
            ],
            [
                'shortname' => 'Incorrect Personal Information',
                'longname'  => 'Incorrect Personal Information',
                'seq'       => 4,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'incorrect-information',
            ],
            [
                'shortname' => 'Does Not Meet Eligibility Criteria',
                'longname'  => 'Does Not Meet Eligibility Criteria',
                'seq'       => 5,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'not-eligible',
            ],
        ]);
    }
}
