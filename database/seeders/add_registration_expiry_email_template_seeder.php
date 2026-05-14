<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_registration_expiry_email_template_seeder extends Seeder
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
                'template_name'      => 'Pharmacy Registration Expiry',
                'template_subject'   => 'Pharmacy Registration Expiry',
                'template_body'      => 'Hi {{firstname}},<br><br> Your registration for the following drug.<b> {{drug_name}} </b>, <b>{{expiry_duration}}</b>  <br> <b>Registration Start Date:</b> {{start_date}}<br> <b>Registration Expiry Date:</b> {{end_date}}<br><br> Please renew your registration before the expiry date to avoid service interruption.',
                'template_signature' => '<br/><p>Regards,<br />Pharmacare Group</p>',
                'can_override'       => 'N',
                'slug'               => 'pharmacy-registration-expiry',
                'template_type_id'   => $parentid,
            ],

        ]);
    }
}
