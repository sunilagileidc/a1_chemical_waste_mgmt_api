<?php
namespace App\Console\Commands;

use App\CustomClass\CustomFunctions;
use App\Mail\RegistrationRejectionMail;
use App\Models\EmailTemplate;
use App\Models\PafDetails;
use App\Models\SystemParameters;
use Illuminate\Console\Command;
use Mail;

class SendDailyPafAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paf:daily-alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send daily PAF alert for off-label & non-compliant cases';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('PAF Daily Alert Cron Started');

        try {

            // ================= GET EMAIL =================
            $recipientEmail = SystemParameters::where('parameter_name', 'PAF_DAILY_ALERT_EMAIL')
                ->value('parameter_value');

            if (! $recipientEmail) {
                $this->error('No email configured in system parameter');
                return;
            }

            // ================= GET TODAY PAF DATA =================
            $today = now()->toDateString();

            $pafs = PafDetails::latestVersion()
                ->whereDate('dispensing_date', $today)
                ->where(function ($q) {
                    $q->where('off_label', 1)
                        ->orWhereHas('nonConformances');
                })
                ->with([
                    'drug:id,drug_name',
                    'indication:id,name',
                    'header:id,patient_no',
                ])
                ->get();

            if ($pafs->isEmpty()) {
                $this->info('No Off-label / Non-compliant PAFs found for today');
                return;
            }

            // ================= BUILD EMAIL TABLE =================
            $rows = '';

            foreach ($pafs as $paf) {

                // Get ALL related IDs (parent + current)
                $pafIds = [$paf->id];

                if ($paf->parent_id) {
                    $pafIds[] = $paf->parent_id;
                }

                // Fetch ALL complaints
                $complaintsData = \App\Models\PAFNonConformance::whereIn('paf_details_id', $pafIds)->get();

                // ================= TYPE =================
                if ($paf->off_label && $complaintsData->count()) {
                    $type = 'Off-label + Non-compliant';
                } elseif ($paf->off_label) {
                    $type = 'Off-label';
                } else {
                    $type = 'Non-compliant';
                }

                // ================= STATUS =================
                $status = $paf->is_reviewed == 1
                    ? '<span style="color:green;font-weight:bold;">Actioned</span>'
                    : '<span style="color:red;font-weight:bold;">Pending</span>';

                // ================= COMPLAINTS =================
                $complaints = '-';

                if ($complaintsData->count()) {
                    $complaints = '<ul style="margin:0;padding-left:15px;">';

                    foreach ($complaintsData as $complaint) {
                        $complaints .= "<li>{$complaint->note}</li>";
                    }

                    $complaints .= '</ul>';
                }

                $rows .= "
                <tr>
                    <td>{$paf->paf_no}</td>
                    <td>" . ($paf->header->patient_no ?? '-') . "</td>
                    <td>" . ($paf->drug->drug_name ?? '-') . "</td>
                    <td>" . ($paf->indication->name ?? '-') . "</td>
                    <td>{$complaints}</td>
                </tr>
            ";
            }

            $table = "
            <table border='1' cellpadding='8' cellspacing='0'
                style='border-collapse:collapse;width:100%;font-size:13px;table-layout:fixed;'>

                <thead style='background:#f5f5f5;'>
                    <tr>
                        <th style='width:10%;'>PAF No</th>
                        <th style='width:20%;'>Patient ID</th>
                        <th style='width:15%;'>Drug</th>
                        <th style='width:15%;'>Indication</th>
                        <th style='width:30%;'>Non-Conformance Notes</th>
                    </tr>
                </thead>

                <tbody>
                    {$rows}
                </tbody>
            </table>
            ";

            // ================= GET TEMPLATE =================
            $template = EmailTemplate::where('template_name', 'PAF Daily Alert Report')->first();

            if (! $template) {
                $this->error('Email template not found');
                return;
            }

            // ================= DATA =================
            $data = [
                'firstname'   => 'Admin', // FIXED
                'date'        => now()->format('d-m-Y'),
                'total_count' => $pafs->count(),
                'table_data'  => $table,
            ];

            // ================= SEND MAIL =================
            Mail::to($recipientEmail)->queue(
                new RegistrationRejectionMail(
                    CustomFunctions::EmailContentParser($template->template_subject, $data),
                    CustomFunctions::EmailContentParser($template->template_body, $data),
                    CustomFunctions::EmailContentParser($template->template_signature, $data),
                    null,
                    null
                )
            );

            $this->info("Mail sent to: {$recipientEmail}");

            // ================= AUDIT =================
            CustomFunctions::audit(
                module: 'PAF Daily Alert',
                action: 'EMAIL SENT',
                referenceId: null,
                referenceTable: 'paf_details',
                oldValues: null,
                newValues: [
                    'email'     => $recipientEmail,
                    'paf_count' => $pafs->count(),
                    'date'      => $today,
                ],
                changedFields: ['email'],
                description: "Daily alert sent with {$pafs->count()} PAF records"
            );

            $this->info('PAF Daily Alert Cron Completed');

        } catch (\Exception $e) {

            \Log::error('PAF Daily Alert Error: ' . $e->getMessage());

            $this->error('Cron failed: ' . $e->getMessage());
        }
    }
}
