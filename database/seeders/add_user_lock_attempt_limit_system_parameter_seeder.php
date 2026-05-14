<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_user_lock_attempt_limit_system_parameter_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_parameter')->insert([
            [
                'parameter_name'  => 'USER_LOCK_ATTEMPT_LIMIT',
                'parameter_value' => '3',
                'description'     => 'Specifies the maximum number of failed login attempts allowed before a user account is automatically locked',
                'is_file_upload'  => 0,
                'status'          => 1,
                'slug'            => 'user-lock-attempt-limit',
            ],
        ]);
    }
}
