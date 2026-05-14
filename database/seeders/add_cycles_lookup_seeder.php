<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_cycles_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'CYCLES',
                'longname'  => 'CYCLES',
                'seq'       => 11,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'cycles',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'CYCLES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => '1 Week',
                'longname'  => '1 Week',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '1-week',
            ],
            [
                'shortname' => '2 Weeks',
                'longname'  => '2 Weeks',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '2-weeks',
            ],
            [
                'shortname' => '3 Weeks',
                'longname'  => '3 Weeks',
                'seq'       => 3,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '3-weeks',
            ],
            [
                'shortname' => '4 Weeks',
                'longname'  => '4 Weeks',
                'seq'       => 4,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '4-weeks',
            ],
        ]);
    }
}
