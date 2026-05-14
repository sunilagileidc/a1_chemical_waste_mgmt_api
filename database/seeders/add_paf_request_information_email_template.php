<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_paf_request_information_email_template extends Seeder
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
                'template_name'      => 'PAF Additional Information Request',
                'template_subject'   => 'Action Required: Additional Information Requested for PAF {{paf_no}}',
                'template_body'      => '
                    <p>Hi {{firstname}},</p>
                    <p>A request for additional information has been raised for the following PAF:</p>
                    <p><strong>PAF No:</strong> {{paf_no}}</p>
                    <p><strong>Patient ID:</strong> {{patient_id}}</p>
                    <p><strong>Drug:</strong> {{drug_name}}</p>
                    <p><strong>Institution:</strong> {{institution}}</p>
                    <p><strong>Request Note:</strong><br>{{request_note}}</p>
                    <p>Please review and take the necessary action.</p>
                ',
                'template_signature' => '<br/><p>Regards,<br/>Pharmacare Group</p>',
                'can_override'       => 'N',
                'slug'               => 'paf-additional-information-request',
                'template_type_id'   => $parentid,
            ],
        ]);
    }
}
