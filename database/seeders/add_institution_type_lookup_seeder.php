<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_institution_type_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'INSTITUTION_TYPE',
                'longname' => 'INSTITUTION_TYPE',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'institution-type',
            ],
        ]);

        // groups
        $inst_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'INSTITUTION_TYPE')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Inpatient Pharmacy',
                'longname' => 'Inpatient Pharmacy',
                'seq' => 1,
                'parent_id' => $inst_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'inpatient-pharmacy',
            ],
            [
                'shortname' => 'Outpatient Pharmacy',
                'longname' => 'Outpatient Pharmacy',
                'seq' => 2,
                'parent_id' => $inst_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'outpatient-pharmacy',
            ],
            [
                'shortname' => 'Homecare',
                'longname' => 'Homecare',
                'seq' => 3,
                'parent_id' => $inst_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'home-care',
            ],
        ]);
    }
}
