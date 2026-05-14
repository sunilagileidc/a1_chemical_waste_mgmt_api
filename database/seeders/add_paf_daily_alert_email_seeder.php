<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_daily_alert_email_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('system_parameter')->insert([
            [
                'parameter_name'  => 'PAF_DAILY_ALERT_EMAIL',
                'parameter_value' => 'test_riskmanagement@pharmacaregroup.co.uk',
                'description'     => 'Email recipients for daily PAF off-label & non-compliant alert',
                'is_file_upload'  => 0,
                'status'          => 1,
                'slug'            => 'paf-daily-alert-email',
            ],
        ]);
    }
}
