<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_off_label_usage_email_template_seeder extends Seeder
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
                'template_name'      => 'Off Label Usage Alert',
                'template_subject'   => 'Off-Label Usage Alert - {{drug_name}} (PAF {{paf_no}})',
                'template_body'      => '
                    <p>Dear {{manufacturer_name}},</p>

                    <p>An off-label usage has been identified and dispensed. Please find the details below:</p>

                    <table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse;">
                        <tr><td><strong>Product</strong></td><td>{{drug_name}}</td></tr>
                        <tr><td><strong>Indication / Under 18</strong></td><td>{{indication}}</td></tr>
                        <tr><td><strong>Patient ID</strong></td><td>{{patient_id}}</td></tr>
                        <tr><td><strong>Unique PAF ID/Number</strong></td><td>{{paf_no}}</td></tr>
                        <tr><td><strong>Dose</strong></td><td>{{dose}}</td></tr>
                        <tr><td><strong>Number of Capsules</strong></td><td>{{capsules}}</td></tr>
                        <tr><td><strong>Pharmacy</strong></td><td>{{pharmacy_name}}</td></tr>
                        <tr><td><strong>Confirmed Off-label and Continuing Use</strong></td><td>{{confirmed_off_label}}</td></tr>
                        <tr><td><strong>Number of times patient has had a prescription (as minor)</strong></td><td>{{minor_patient_prescription_count}}</td></tr>
                        <tr><td><strong>Number of times patient has received this brand for this indication</strong></td><td>{{brand_prescription_count}}</td></tr>
                    </table>

                    <p>Please review and take necessary action.</p>
                ',
                'template_signature' => '<br/><p>Regards,<br/>Pharmacare Group</p>',
                'can_override'       => 'N',
                'slug'               => 'off-label-usage-alert',
                'template_type_id'   => $parentid,
            ],
        ]);
    }
}
