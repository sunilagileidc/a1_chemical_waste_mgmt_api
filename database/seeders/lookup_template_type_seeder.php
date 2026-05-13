<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class lookup_template_type_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
               DB::table('lookups')->insert([
            [
                'shortname' => 'TEMPLATE_TYPE',
                'longname' => 'TEMPLATE_TYPE',
                'seq' => 1,
                'parent_id' => 0,
                'icon' => '',
                'status' => 1,
                'slug' => 'template-type',
            ],
        ]);

        $parent = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'TEMPLATE_TYPE')
            ->pluck('id');
        $quotes = ['[', ']'];
        $parentid = str_replace($quotes, '', $parent);

        DB::table('lookups')->insert([
            [
                'shortname' => 'Email',
                'longname' => 'Email',
                'seq' => 1,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'Email',
            ],
            [
                'shortname' => 'SMS/Notification',
                'longname' => 'SMS/Notification',
                'seq' => 2,
                'parent_id' => $parentid,
                'icon' => '',
                'status' => 1,
                'slug' => 'sms-notification',
            ],
        ]);
    }
}
