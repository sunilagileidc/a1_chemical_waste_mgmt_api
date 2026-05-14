<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_menu_dashboard extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Dashboard',
                'href'      => 'dashboard',
                'parent_id' => 0,
                'seq'       => 1,
                'icon'      => 'mdi mdi-view-dashboard',
                'slug'      => 'dashboard',
            ],
            [
                'title'     => 'Configuration',
                'href'      => '#',
                'parent_id' => 0,
                'seq'       => 2,
                'icon'      => 'mdi mdi-cog',
                'slug'      => 'configuration',
            ],
            [
                'title'     => 'Users',
                'href'      => 'users',
                'parent_id' => 0,
                'seq'       => 3,
                'icon'      => 'mdi mdi-account-multiple',
                'slug'      => 'users',
            ],
            [
                'title'     => 'Registered Users',
                'href'      => 'registration_list',
                'parent_id' => 0,
                'seq'       => 4,
                'icon'      => 'mdi mdi-account-multiple',
                'slug'      => 'registration-list',
            ],
        ]);
        $parentid = DB::table('menus')
            ->select('id')
            ->where('title', '=', 'Configuration')
            ->value('id');

        DB::table('menus')->insert([
            [
                'title'     => 'Roles',
                'href'      => 'roles',
                'parent_id' => $parentid,
                'seq'       => 1,
                'icon'      => '',
                'slug'      => 'roles',
            ],
            [
                'title'     => 'Menus',
                'href'      => 'menus',
                'parent_id' => $parentid,
                'seq'       => 2,
                'icon'      => '',
                'slug'      => 'menus',
            ],
            [
                'title'     => 'Action Master',
                'href'      => 'action_master',
                'parent_id' => $parentid,
                'seq'       => 3,
                'icon'      => '',
                'slug'      => 'action-master',
            ],
            [
                'title'     => 'Lookups',
                'href'      => 'lookups',
                'parent_id' => $parentid,
                'seq'       => 4,
                'icon'      => '',
                'slug'      => 'lookups',
            ],
            [
                'title'     => 'System Parameter',
                'href'      => 'system_parameter',
                'parent_id' => $parentid,
                'seq'       => 5,
                'icon'      => '',
                'slug'      => 'system-parameter',
            ],
            [
                'title'     => 'Countries',
                'href'      => 'countries',
                'parent_id' => $parentid,
                'seq'       => 6,
                'icon'      => '',
                'slug'      => 'countries',
            ],
            [
                'title'     => 'Email Templates',
                'href'      => 'email_template',
                'parent_id' => $parentid,
                'seq'       => 7,
                'icon'      => '',
                'slug'      => 'email-template',
            ],
            [
                'title'     => 'Documents',
                'href'      => 'documents',
                'parent_id' => $parentid,
                'seq'       => 8,
                'icon'      => '',
                'slug'      => 'documents',
            ],
        ]);
    }
}
