<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_general_group_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'GENERAL_GROUP',
                'longname'  => 'GENERAL_GROUP',
                'seq'       => 1,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'general-group',
            ],
        ]);

        // groups
        $doc_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'GENERAL_GROUP')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Training Material',
                'longname'  => 'Training Material',
                'seq'       => 1,
                'parent_id' => $doc_parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'training-material',
            ],
        ]);
    }
}
