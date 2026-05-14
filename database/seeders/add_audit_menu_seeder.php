<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_audit_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Audit Log',
                'href'      => 'audit_log',
                'parent_id' => 0,
                'seq'       => 2,
                'icon'      => 'mdi mdi-format-list-text',
                'slug'      => 'audit-log',
            ],
        ]);
    }
}
