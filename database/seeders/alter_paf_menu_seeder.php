<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class alter_paf_menu_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('menus')
            ->where('slug', 'pafs')
            ->update([
                'href' => 'paf_report',
                'updated_at' => now(),
            ]);
    }
}
