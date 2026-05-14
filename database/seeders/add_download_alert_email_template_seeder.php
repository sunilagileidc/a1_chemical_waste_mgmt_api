<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_download_alert_email_template_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $parentid = DB::table('lookups')
            ->where('shortname', 'Email')
            ->value('id');

        DB::table('email_templates')->insert([
            [
                'template_name'      => 'Pregnancy Form Download Alert',
                'template_subject'   => 'Alert: Pregnancy Reporting Form Downloaded - {{document_name}}',
                'template_body'      => '
                <p>Hi Admin,</p>

                <p>This is to inform you that a <strong>Pregnancy Reporting Form</strong> has been downloaded.</p>

                <p><strong>Details:</strong></p>
                <ul>
                    <li><strong>Document Name:</strong> {{document_name}}</li>
                    <li><strong>Downloaded By:</strong> {{user_name}}</li>
                    <li><strong>User Role:</strong> {{user_role}}</li>
                    <li><strong>Email:</strong> {{user_email}}</li>
                    <li><strong>Date & Time:</strong> {{download_time}}</li>
                </ul>

                <p>Please review if any action is required.</p>
            ',
                'template_signature' => '<br/><p>Regards,<br/>Pharmacare Group</p>',
                'can_override'       => 'N',
                'slug'               => 'pregnancy-form-download-alert',
                'template_type_id'   => $parentid,
            ],
        ]);
    }
}
