<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_wholesaler_role_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            [
                'rolename' => 'Wholesaler',
                'role_display_name' => 'Wholesaler',
                'roledescription' => 'Wholesaler',
                'slug' => 'wholesaler',
            ],
        ]);
    }
}
