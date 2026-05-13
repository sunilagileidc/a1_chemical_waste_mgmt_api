<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_system_parameter_basic_data extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
            DB::table('system_parameter')->insert([
            [
                'parameter_name' => 'APP_LOGO',
                'parameter_value' => '',
                'description' => 'Default Image for the App',
                'is_file_upload' => 1,
                'status' => 1,
                'slug' => 'app_logo',
            ],
            [
                'parameter_name' => 'LOGIN_OTP_ENABLED',
                'parameter_value' => 'No',
                'description' => 'Allow users to log in using an OTP sent to their email.',
                'is_file_upload' => 0,
                'status' => 0,
                'slug' => 'login-otp-enabled',
            ],
        ]);
    }
}
