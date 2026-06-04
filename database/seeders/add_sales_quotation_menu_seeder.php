<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_sales_quotation_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')->insert([
            [
                'title'     => 'Sales Quotations',
                'href'      => 'sales_quotations',
                'parent_id' => 0,
                'seq'       => 17,
                'icon'      => 'file-document-outline',
                'slug'      => 'sales_quotations',
            ],
        ]);
    }
}
