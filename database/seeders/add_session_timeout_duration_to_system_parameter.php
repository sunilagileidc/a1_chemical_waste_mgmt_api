<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_session_timeout_duration_to_system_parameter extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_parameter')->insert([
            [
                'parameter_name'  => 'SESSION_TIMEOUT_DURATION',
                'parameter_value' => '10',
                'description'     => 'Session Timeout Duration in minutes',
                'is_file_upload'  => 0,
                'status'          => 1,
                'slug'            => 'session-timeout-duration',
            ],
            [
                'parameter_name'  => 'SESSION_WARNING_DURATION',
                'parameter_value' => '2',
                'description'     => 'Session Warning Duration in minutes',
                'is_file_upload'  => 0,
                'status'          => 1,
                'slug'            => 'session-warning-duration',
            ],
        ]);
    }
}
