<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_pharmacies_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Pharmacies',
                'href'      => 'pharmacies',
                'parent_id' => 0,
                'seq'       => 8,
                'icon'      => 'mdi mdi-medication-outline',
                'slug'      => 'pharmacies',
            ],
            [
                'title'     => 'Hospitals',
                'href'      => 'hospitals',
                'parent_id' => 0,
                'seq'       => 9,
                'icon'      => 'mdi mdi-hospital-building',
                'slug'      => 'hospitals',
            ],

        ]);
    }
}
