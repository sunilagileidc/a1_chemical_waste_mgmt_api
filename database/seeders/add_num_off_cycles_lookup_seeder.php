<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_num_off_cycles_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'NUMBER_OF_CYCLES',
                'longname'  => 'NUMBER_OF_CYCLES',
                'seq'       => 11,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'number-of-cycles',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'NUMBER_OF_CYCLES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => '1 Cycle',
                'longname'  => '1 Cycle',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '1-cycle',
            ],
            [
                'shortname' => '2 Cycles',
                'longname'  => '2 Cycles',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '2-weeks',
            ],
            [
                'shortname' => '3 Cycles',
                'longname'  => '3 Cycles',
                'seq'       => 3,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '3-cycles',
            ],
            [
                'shortname' => '4 Cycles',
                'longname'  => '4 Cycles',
                'seq'       => 4,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '4-cycles',
            ],
        ]);
    }
}
