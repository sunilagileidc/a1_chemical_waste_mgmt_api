<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_action_master_btns_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('action_master')->insert([
            [
                'action_name' => 'CONNECTED USERS',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'others-connected-users',
                'created_by' => 1,
                'updated_by' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'CONNECTED NURSES',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'others-connected-nurses',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'TRAINING DOCUMENTS',
                'category' => 'Others',
                'description' => null,
                'status' => 1,
                'slug' => 'others-training-documents',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'action_name' => 'DOWNLOAD PAF',
                'category' => 'PAF',
                'description' => null,
                'status' => 1,
                'slug' => 'paf-download-paf',
                'created_by' => 1,
                'updated_by' => null,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],

        ]);
    }
}
