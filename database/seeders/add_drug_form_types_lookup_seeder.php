<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_drug_form_types_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'DRUG_FORM_TYPES',
                'longname'  => 'DRUG_FORM_TYPES',
                'seq'       => 12,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'drug-form-types',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'DRUG_FORM_TYPES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Capsule',
                'longname'  => 'Capsule',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'capsule',
            ],
            [
                'shortname' => 'Tablet',
                'longname'  => 'Tablet',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'tablet',
            ],
        ]);
    }
}
