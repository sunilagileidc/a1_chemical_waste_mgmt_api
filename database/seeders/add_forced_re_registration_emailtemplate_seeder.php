<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_forced_re_registration_emailtemplate_seeder extends Seeder
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
                'template_name'      => 'Forced Re-registration Notice',
                'template_subject'   => 'Re-register {{drug_name}}',
                'template_body'      => '
            <p>Hi {{firstname}},</p>
            <p>Your registration for <strong>{{drug_name}}</strong> has been marked as <strong>expired</strong> and requires re-registration.</p>
            <p>This action has been initiated by the admin.</p>
            <p><strong>Reason:</strong> {{reason}}</p>
            <p>Please complete the re-registration at the earliest to continue using the service.</p>
        ',
                'template_signature' => '<br/><p>Regards,<br />Pharmacare Team</p>',
                'can_override'       => 'N',
                'slug'               => 'forced-re-registration',
                'template_type_id'   => $parentid,
            ],
        ]);
    }
}
