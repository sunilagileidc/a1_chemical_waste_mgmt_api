<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_email_templates_seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $parent = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'Email')
            ->pluck('id');
        $quotes = ['[', ']'];
        $parentid = str_replace($quotes, '', $parent);

        DB::table('email_templates')->insert([
            [
                'template_name' => 'OTP Verification',
                'template_subject' => 'OTP Verification',
                'template_body' => 'Hi {{firstname}},<br/>Your Verification Code is {{otp}}.  Please use the same to complete the registration process.',
                'template_signature' => '<br/><p>Regards,<br />AgileIDC</p>',
                'can_override' => 'N',
                'slug' => 'ot-p',
                'template_type_id' => $parentid,
            ],
            [
                'template_name' => 'Forgot Password',
                'template_subject' => 'Forgot Password',
                'template_body' => 'Hi {{firstname}}, <br/>Your Verification Code is {{otp}}. This code will expire in 5 minutes<br/>',
                'template_signature' => '<p>Regards,<br />AgileIDC</p>',
                'can_override' => 'N',
                'slug' => 'forgot-pass',
                'template_type_id' => $parentid,
            ],
            [
                'template_name' => 'Send Credentials',
                'template_subject' => 'Send Credentials',
                'template_body' => '<p style=\"text-align:left\">Hi {{firstname}}, <br/> Welcome to AgileIDC.  We have created an account and the username is the registered email and the password is {{password}}.</p>',
                'template_signature' => '<br/><p>Regards,<br />AgileIDC</p>',
                'can_override' => 'N',
                'slug' => 'resend-credentials',
                'template_type_id' => $parentid,
            ],
            [
                'template_name' => 'Resend OTP',
                'template_subject' => 'Resend OTP',
                'template_body' => '<p>Hi {{firstname}},</p> <p> Resend OTP is {{otp}}.</p> <p>This Code will expire in 5 minutes',
                'template_signature' => '<p>Regards,<br/>AgileIDC</p>',
                'can_override' => 'N',
                'slug' => 'resend-otp',
                'template_type_id' => $parentid,
            ],
        ]);
    }
}
