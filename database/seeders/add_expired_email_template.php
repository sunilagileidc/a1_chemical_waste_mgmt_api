<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_expired_email_template extends Seeder
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
                'template_name'      => 'Expired due to Document Update',
                'template_subject'   => 'Document Update Notice - {{document_title}}',
                'template_body'      => '<p>Hi {{firstname}},</p><p>We would like to inform you that a document has been updated in the system.</p><p><strong>Document Title: </strong> <strong>{{document_title}}</strong></p><p><strong>Related Drug:  {{drug_name}}</strong></p><p>Due to this update, your related registration/data has been marked as <strong>expired</strong>.</p><p></p><p><strong>Reason: {{expiry_reason}}</strong></p><p><br></p><p>Please review the updated document and take necessary action to ensure continuity of services.</p>',
                'template_signature' => '<br/><p>Regards,<br />Pharmacare Group</p>',
                'can_override'       => 'N',
                'slug'               => 'expired-due-to-document-update',
                'template_type_id'   => $parentid,
            ],

        ]);
    }
}
