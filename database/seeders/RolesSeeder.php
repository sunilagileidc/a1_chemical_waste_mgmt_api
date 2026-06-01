<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Keep only SuperUser
        DB::table('roles')
            ->where('rolename', '!=', 'SuperUser')
            ->delete();

        // Insert new roles if they don't exist
        $roles = [
            [
                'rolename' => 'Admin',
                'role_display_name' => 'Admin',
                'roledescription' => 'Administrator',
                'status' => 1,
                'slug' => 'admin',
            ],
            [
                'rolename' => 'Customer',
                'role_display_name' => 'Customer',
                'roledescription' => 'Customer',
                'status' => 1,
                'slug' => 'customer',
            ],
            [
                'rolename' => 'Supplier',
                'role_display_name' => 'Supplier',
                'roledescription' => 'Supplier',
                'status' => 1,
                'slug' => 'supplier',
            ],
            [
                'rolename' => 'Haulier',
                'role_display_name' => 'Haulier',
                'roledescription' => 'Haulier',
                'status' => 1,
                'slug' => 'haulier',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['rolename' => $role['rolename']],
                array_merge($role, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}