<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_role_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            [
                'rolename' => 'SuperUser',
                'role_display_name' => 'SuperUser',
                'roledescription' => 'SuperUser',
                'slug' => 'superuser',
            ],
            [
                'rolename' => 'MallAdmin',
                'role_display_name' => 'Mall Admin',
                'roledescription' => 'Mall Admin',
                'slug' => 'mall-admin',
            ],
            [
                'rolename' => 'StoreAdmin',
                'role_display_name' => 'Store Admin',
                'roledescription' => 'Store Admin',
                'slug' => 'store-admin',
            ]
        ]);
    }
}
