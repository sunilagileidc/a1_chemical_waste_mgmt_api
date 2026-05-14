<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_appr_rej_email_template_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentid = DB::table('lookups')
            ->select('id')
            ->where('shortname', '=', 'Email')
            ->value('id');
        $quotes = ['[', ']'];

        DB::table('email_templates')->insert([
            [
                'template_name' => 'Account Approved',
                'template_subject' => 'Your Account Has Been Approved',
                'template_body' => 'Hi {{firstname}}, <br/>Your account has been <strong>verified and approved</strong>. <br/> You can now log in and start using your account. <a href="{{app_url}}" target="_blank">Click here to login</a><br/>',
                'template_signature' => '<p>Regards,<br />Team ePAF</p>',
                'can_override' => 'N',
                'slug' => 'account-approved',
                'template_type_id' => $parentid,
            ],
            [
                'template_name' => 'Account Rejected',
                'template_subject' => 'Your Account Has Been Rejected',
                'template_body' => 'Hi {{firstname}}, <br/>Your account has been <strong>rejected</strong>. <br/>Reason: {{rejection_reason}} <br/>',
                'template_signature' => '<p>Regards,<br />Team ePAF</p>',
                'can_override' => 'N',
                'slug' => 'account-rejected',
                'template_type_id' => $parentid,
            ],
            [
                'template_name' => 'Account Status Update',
                'template_subject' => 'Account Status Update',
                'template_body' => 'Hi {{firstname}}, <br/><br/>',
                'template_signature' => '<p>Regards,<br />Team ePAF</p>',
                'can_override' => 'N',
                'slug' => 'account-status-update',
                'template_type_id' => $parentid,
            ],
        ]);
    }
}
