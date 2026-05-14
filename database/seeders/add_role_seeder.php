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
                'rolename' => 'Prescriber',
                'role_display_name' => 'Prescriber',
                'roledescription' => 'Prescriber',
                'slug' => 'prescriber',
            ],
            [
                'rolename' => 'Pharmacist',
                'role_display_name' => 'Pharmacist',
                'roledescription' => 'Pharmacist',
                'slug' => 'pharmacist',
            ]
        ]);
    }
}
