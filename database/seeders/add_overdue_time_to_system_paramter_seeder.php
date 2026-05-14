<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_overdue_time_to_system_paramter_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_parameter')->insert([
            [
                'parameter_name' => 'PAF_OVERDUE_TIME',
                'parameter_value' => '16:30',
                'description' => 'paf overdue time should be next day before specified time',
                'is_file_upload' => 0,
                'status' => 1,
                'slug' => 'paf-overdue-time',
            ],
        ]);
    }
}
