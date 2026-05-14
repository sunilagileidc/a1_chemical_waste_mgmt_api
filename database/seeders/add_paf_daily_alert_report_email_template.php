<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_daily_alert_report_email_template extends Seeder
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
                'template_name'      => 'PAF Daily Alert Report',
                'template_subject'   => 'Daily Alert: Off-label & Non-compliant PAFs - {{date}}',
                'template_body'      => '
                    <p>Hi {{firstname}},</p>
                    <p>Please find below the daily alert report for non-compliant and off-label PAFs.</p>
                    <p><strong>Date:</strong> {{date}}</p>
                    <p><strong>Total Records:</strong> {{total_count}}</p>
                    <br>
                    {{table_data}}
                    <br>
                    <p>Please review and take necessary action if required.</p>
                ',
                'template_signature' => '<br/><p>Regards,<br/>Pharmacare Group</p>',
                'can_override'       => 'N',
                'slug'               => 'paf-daily-alert-report',
                'template_type_id'   => $parentid,
            ],
        ]);
    }
}
