<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_action_master_values_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('action_master')->insert([
            [
                'action_name' => 'RENEW PAF',
                'category'    => 'PAF',
                'description' => null,
                'status'      => 1,
                'slug'        => 'paf-renew-paf',
                'created_by'  => 1,
                'updated_by'  => 1,
                'created_at'  => Carbon::now(),
                'updated_at'  => Carbon::now(),
            ]
        ]);
    }
}
