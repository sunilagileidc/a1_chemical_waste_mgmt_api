<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_drugs_strength_data_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'DRUG_STRENGTH',
                'longname' => 'DRUG_STRENGTH',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'drug-strength',
            ],
        ]);

        // groups
        $drug_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'DRUG_STRENGTH')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => '5',
                'longname' => '5mg',
                'seq' => 1,
                'parent_id' => $drug_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => '5-mg',
            ],
            [
                'shortname' => '10',
                'longname' => '10mg',
                'seq' => 2,
                'parent_id' => $drug_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => '10-mg',
            ],
            [
                'shortname' => '15',
                'longname' => '15mg',
                'seq' => 3,
                'parent_id' => $drug_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => '15-mg',
            ],
            [
                'shortname' => '20',
                'longname' => '20mg',
                'seq' => 4,
                'parent_id' => $drug_parentid,
                'icon' => '',
                'status' => 1,
                'slug' => '20-mg',
            ],
        ]);
    }
}
