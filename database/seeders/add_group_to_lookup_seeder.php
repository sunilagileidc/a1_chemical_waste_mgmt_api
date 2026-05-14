<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_group_to_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'DOCUMENT_GROUP',
                'longname'  => 'DOCUMENT_GROUP',
                'seq'       => 1,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'document-GROUP',
            ]
        ]);

        // groups
        $doc_parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'DOCUMENT_GROUP')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => 'Training Document',
                'longname'  => 'Training Document',
                'seq'       => 1,
                'parent_id' => $doc_parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'training-document',
            ],
            [
                'shortname' => 'Prescriptions',
                'longname'  => 'Prescriptions',
                'seq'       => 2,
                'parent_id' => $doc_parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'prescriptions',
            ],
        ]);
    }
}
