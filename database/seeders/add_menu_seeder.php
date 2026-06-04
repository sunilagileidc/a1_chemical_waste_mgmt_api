<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title' => 'Customers',
                'href' => 'customers',
                'parent_id' => 0,
                'seq' => 13,
                'icon' => 'mdi mdi-account-tie',
                'slug' => 'customers',
            ],
            [
                'title' => 'Suppliers',
                'href' => 'suppliers',
                'parent_id' => 0,
                'seq' => 14,
                'icon' => 'mdi mdi-account-supervisor',
                'slug' => 'suppliers',
            ],
                [
                    'title' => 'Hauliers',
                    'href' => 'hauliers',
                    'parent_id' => 0,
                    'seq' => 15,
                    'icon' => 'mdi mdi-truck',
                    'slug' => 'hauliers',
                ],
                [
                    'title' => 'Waste Stream',
                    'href' => 'waste_streams',
                    'parent_id' => 0,
                    'seq' => 16,
                    'icon' => 'mdi mdi-recycle',
                    'slug' => 'waste_streams',
                ],
        ]);
    }
}
