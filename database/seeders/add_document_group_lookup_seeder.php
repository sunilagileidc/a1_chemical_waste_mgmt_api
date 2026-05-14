<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_document_group_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentid = DB::table('lookups')
            ->where('shortname', 'DOCUMENT_GROUP')
            ->value('id');

        // Delete existing child records
        DB::table('lookups')
            ->where('parent_id', $parentid)
            ->delete();

        // Insert new records
        DB::table('lookups')->insert([
            [
                'shortname' => 'RAF',
                'longname'  => 'RAF',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'raf',
            ],
            [
                'shortname' => 'ARMM',
                'longname'  => 'ARMM',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'armm',
            ],
        ]);
    }
}
