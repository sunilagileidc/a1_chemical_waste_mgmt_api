<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_login_otp_verification_email_template_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parent = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'Email')
            ->pluck('id');
        $quotes = ['[', ']'];
        $parentid = str_replace($quotes, '', $parent);

        DB::table('email_templates')->insert([
            [
                'template_name' => 'Login OTP Verification',
                'template_subject' => 'Login OTP Verification',
                'template_body' => 'Hi {{firstname}}, <br/>Your Login Verification Code is {{otp}}. This code will expire in 5 minutes<br/>',
                'template_signature' => '<p>Regards,<br />AgileIDC</p>',
                'can_override' => 'N',
                'slug' => 'login-otp-verification',
                'template_type_id' => $parentid,
            ],
        ]);
    }
}
