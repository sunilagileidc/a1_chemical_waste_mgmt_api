<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_documents_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('menus')->insert([
            [
                'title'     => 'PAF Documents',
                'href'      => 'paf_documents',
                'parent_id' => 0,
                'seq'       => 12,
                'icon'      => 'mdi mdi-file-document-multiple',
                'slug'      => 'paf-documents',
            ],
        ]);
    }
}
