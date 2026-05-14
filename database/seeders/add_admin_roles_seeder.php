<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_admin_roles_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'rolename'          => 'PCG Admin',
                'role_display_name' => 'PCG Admin',
                'roledescription'   => 'PCG Admin',
                'slug'              => 'pcg-admin',
            ],
            [
                'rolename'          => 'PCG User',
                'role_display_name' => 'PCG User',
                'roledescription'   => 'PCG User',
                'slug'              => 'pcg-user',
            ],
            [
                'rolename'          => 'Nurse',
                'role_display_name' => 'Nurse',
                'roledescription'   => 'Nurse',
                'slug'              => 'nurse',
            ],
        ]);
    }
}
