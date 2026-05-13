<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class superuser_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parent = DB::table('roles')
            ->select('id')
            ->where('rolename', '=', 'SuperUser')
            ->pluck('id');
        $quotes = ['[', ']'];
        $parentid = str_replace($quotes, '', $parent);

        $menuids = DB::table('menus')
            ->select('id')
            ->whereIn('title', ['Dashboard', 'Configuration', 'Roles', 'Menus'])
            ->pluck('id');

        if ($menuids) {
            foreach ($menuids as $menuid) {
                DB::table('role_menu')->insert([
                    [
                        'role_id' => $parentid,
                        'menu_id' => $menuid,
                        'status' => 1,
                    ],
                ]);
            }
        }
    }
}
