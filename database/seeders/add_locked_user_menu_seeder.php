<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_locked_user_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Locked Users',
                'href'      => 'locked_users',
                'parent_id' => 0,
                'seq'       => 7,
                'icon'      => 'mdi mdi-account-key',
                'slug'      => 'locked_users',
            ],
        ]);
    }
}
