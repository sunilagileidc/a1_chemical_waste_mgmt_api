<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_hospital_inside_instittion_type_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $inst_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'INSTITUTION_TYPE')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Hospital',
                'longname'  => 'Hospital',
                'seq'       => 4,
                'parent_id' => $inst_parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'hospital',
            ],

        ]);
    }
}
