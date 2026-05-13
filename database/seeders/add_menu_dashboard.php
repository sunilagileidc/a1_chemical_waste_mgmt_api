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
                'is_header' => 0,
                'title' => 'Dashboard',
                'href' => 'dashboard',
                'parent_id' => 0,
                'seq' => 1,
                'icon' => 'mdi mdi-view-dashboard',
                'slug' => 'dashboard',
            ],
            [
                'is_header' => 0,
                'title' => 'Configuration',
                'href' => '#',
                'parent_id' => 0,
                'seq' => 2,
                'icon' => 'mdi mdi-cog',
                'slug' => 'configuration',
            ],
            [
                'is_header' => 0,
                'title' => 'Users',
                'href' => 'users',
                'parent_id' => 0,
                'seq' => 3,
                'icon' => 'mdi mdi-account-multiple',
                'slug' => 'users',
            ],

        ]);
        $locale = DB::table('menus')
            ->select('id')
            ->where('title', '=', 'Configuration')
            ->pluck('id');
        $quotes = ['[', ']'];
        $parentid = str_replace($quotes, '', $locale);

        DB::table('menus')->insert([
            [
                'is_header' => 0,
                'title' => 'Roles',
                'href' => 'roles',
                'parent_id' => $parentid,
                'seq' => 1,
                'icon' => '',
                'slug' => 'roles',
            ],
            [
                'is_header' => 0,
                'title' => 'Menus',
                'href' => 'menus',
                'parent_id' => $parentid,
                'seq' => 2,
                'icon' => '',
                'slug' => 'menus',
            ],
            [
                'is_header' => 0,
                'title' => 'Lookups',
                'href' => 'lookups',
                'parent_id' => $parentid,
                'seq' => 3,
                'icon' => '',
                'slug' => 'lookups',
            ],
            [
                'is_header' => 0,
                'title' => 'System Parameter',
                'href' => 'system_parameter',
                'parent_id' => $parentid,
                'seq' => 4,
                'icon' => '',
                'slug' => 'system-parameter',
            ],
            [
                'is_header' => 0,
                'title' => 'Countries',
                'href' => 'countries',
                'parent_id' => $parentid,
                'seq' => 5,
                'icon' => '',
                'slug' => 'countries',
            ],
            [
                'is_header' => 0,
                'title' => 'Email Templates',
                'href' => 'email_template',
                'parent_id' => $parentid,
                'seq' => 6,
                'icon' => '',
                'slug' => 'email-template',
            ],
        ]);
    }
}
