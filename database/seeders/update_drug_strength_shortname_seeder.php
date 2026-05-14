<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class update_drug_strength_shortname_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('lookups')
            ->where('slug', '5-mg')
            ->update(['shortname' => '5mg']);

        DB::table('lookups')
            ->where('slug', '10-mg')
            ->update(['shortname' => '10mg']);

        DB::table('lookups')
            ->where('slug', '15-mg')
            ->update(['shortname' => '15mg']);

        DB::table('lookups')
            ->where('slug', '20-mg')
            ->update(['shortname' => '20mg']);
    }
}
