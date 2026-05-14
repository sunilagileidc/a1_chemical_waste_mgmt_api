<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class add_paf_final_reminder_notice_email_template extends Seeder
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
                'template_name'    => 'PAF Final Reminder Notice',
                'template_subject' => 'Final Notice – Deregistration Warning (PAF {{paf_no}})',
                'template_body'    => '
                    <p>Dear {{firstname}},</p>
                    <p>This is the <strong>final reminder</strong> regarding the pending request for additional information.</p>
                    <p><strong>PAF No:</strong> {{paf_no}}</p>
                    <p><strong>Patient ID:</strong> {{patient_id}}</p>
                    <p><strong>Drug:</strong> {{drug_name}}</p>
                    <p style="color:red;"><strong>
                    Failure to respond within 5 business days will result in account deactivation.
                    </strong></p>

                    <p>Please take immediate action.</p>
                ',
            ],
        ]);
    }
}
