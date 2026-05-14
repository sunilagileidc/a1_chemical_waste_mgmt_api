<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_supplier_sales_data_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        DB::table('menus')->insert([
            [
                'title'     => 'Supplier Sales Data',
                'href'      => 'supplier_sales_data',
                'parent_id' => 0,
                'seq'       => 13,
                'icon'      => 'mdi mdi-folder-upload',
                'slug'      => 'supplier-sales-data',
            ],
        ]);
    }
}
