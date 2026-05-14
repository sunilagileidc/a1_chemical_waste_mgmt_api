<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_capsules_lookup_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')->insert([
            [
                'shortname' => 'CAPSULES',
                'longname'  => 'CAPSULES',
                'seq'       => 10,
                'parent_id' => 0,
                'icon'      => '',
                'status'    => 1,
                'slug'      => 'capsules',
            ],
        ]);

        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'CAPSULES')
            ->value('id');

        DB::table('lookups')->insert([
            [
                'shortname' => '7 Capsules',
                'longname'  => '7 Capsules',
                'seq'       => 1,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '7-capsules',
            ],
            [
                'shortname' => '14 Capsules',
                'longname'  => '14 Capsules',
                'seq'       => 2,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '14-capsules',
            ],
            [
                'shortname' => '21 Capsules',
                'longname'  => '21 Capsules',
                'seq'       => 3,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '21-capsules',
            ],
            [
                'shortname' => '28 Capsules',
                'longname'  => '28 Capsules',
                'seq'       => 4,
                'parent_id' => $parentid,
                'icon'      => '',
                'status'    => 1,
                'slug'      => '28-capsules',
            ],
        ]);
    }
}
