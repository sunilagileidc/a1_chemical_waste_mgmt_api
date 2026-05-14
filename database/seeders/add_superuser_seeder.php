<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class add_superuser_seeder extends Seeder
{
    /**
     * Run the database seeds.
     * database seeder for superuser
     *
     * @return void
     */
    public function run()
    {
        $locale = DB::table('roles')
            ->select('id')
            ->where('rolename', '=', 'SuperUser')
            ->pluck('id');
        $quotes = ['[', ']'];
        $parentid = str_replace($quotes, '', $locale);

        $password = 'Agileidc@456';
        $password = Hash::make($password);
        DB::table('users')->insert([
            [
                'salutation' => 'Mr',
                'name' => 'Super',
                'lastname' => 'User',
                'gender' => 'Male',
                'email' => 'superuser@agileidc.com',
                'password' => $password,
                'mobile' => '99999999999',
                'role_id' => 1,
                'slug' => 'Super-User-1',
            ],

        ]);
    }
}
