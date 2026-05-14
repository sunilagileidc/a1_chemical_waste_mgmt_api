<?php
namespace App\Console\Commands;

use App\CustomClass\CustomFunctions;
use App\Mail\RegistrationRejectionMail;
use App\Models\EmailTemplate;
use App\Models\PafDetails;
use App\Models\PafRequestInformation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class SendPafRequestReminder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paf:request-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder emails for PAF additional info request';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('PAF Request Reminder Cron Started');

        $records = PafRequestInformation::where('is_closed', 0)->get();

        foreach ($records as $record) {

            $paf = PafDetails::with(['drug', 'institutions', 'header'])
                ->find($record->paf_detail_id);

            if (! $paf) {
                continue;
            }

            // ================= URGENCY =================
            $isUrgent     = ($paf->patient_category === 'WCBP');
            $intervalDays = $isUrgent ? 5 : 10;

            $lastSent = $record->last_reminder_sent_at
                ? Carbon::parse($record->last_reminder_sent_at)
                : Carbon::parse($record->created_at);

            $today = Carbon::today();

            // ================= BUSINESS DAYS =================
            $businessDaysPassed = 0;
            $tempDate           = $lastSent->copy();

            while ($tempDate->lt($today)) {
                $tempDate->addDay();

                if (! $tempDate->isWeekend()) {
                    $businessDaysPassed++;
                }
            }

            // ================= CHECK INTERVAL =================
            if ($businessDaysPassed < $intervalDays) {
                continue;
            }

            // ================= GET USERS =================
            $users = User::whereIn('id', $record->requested_users ?? [])->get();

            // ================= FINAL REMINDER SENT? =================
            if ($record->reminder_count >= 3) {

                // Check 5 business day grace after final
                $finalSent = Carbon::parse($record->last_reminder_sent_at);

                $graceDays = 0;
                $tempDate  = $finalSent->copy();

                while ($tempDate->lt($today)) {
                    $tempDate->addDay();

                    if (! $tempDate->isWeekend()) {
                        $graceDays++;
                    }
                }

                if ($graceDays >= 5) {

                    foreach ($users as $user) {
                        $user->update(['status' => 0]);
                    }

                    $record->update(['is_closed' => 1]);

                    CustomFunctions::audit(
                        module: 'PAF Request Information',
                        action: 'USER DEACTIVATED',
                        referenceId: $record->id,
                        referenceTable: 'paf_request_information',
                        oldValues: null,
                        newValues: ['status' => 'deactivated'],
                        changedFields: ['status'],
                        description: 'User deactivated after no response to final reminder'
                    );

                    $this->warn("Users deactivated for PAF ID: {$record->paf_detail_id}");
                }

                continue;
            }

            // ================= TEMPLATE LOGIC =================
            switch ($record->reminder_count) {
                case 0:
                    $templateName = 'PAF Reminder 1';
                    break;
                case 1:
                    $templateName = 'PAF Reminder 2';
                    break;
                case 2:
                    $templateName = 'PAF Final Reminder Notice';
                    break;
                default:
                    continue 2;
            }

            $emailTemplate = EmailTemplate::where('template_name', $templateName)->first();

            // ================= SEND MAIL =================
            foreach ($users as $user) {

                if (
                    $emailTemplate &&
                    ($emailTemplate->is_mandatory == 1 ||
                        ($emailTemplate->is_mandatory == 0 && $user->email_subscription == 1))
                ) {

                    $userdata = [
                        'firstname'    => $user->full_name,
                        'paf_no'       => $record->paf_no,
                        'patient_id'   => $record->patient_id,
                        'drug_name'    => $paf->drug->drug_name ?? '',
                        'institution'  => $paf->institutions->name ?? '',
                        'request_note' => $record->request_note,
                        'type'         => $isUrgent ? 'Urgent' : 'Standard',
                    ];

                    Mail::to($user->email)->queue(
                        new RegistrationRejectionMail(
                            CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata),
                            CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata),
                            CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata),
                            null,
                            null
                        )
                    );
                }
            }

            // ================= UPDATE COUNT =================
            $newReminderCount = $record->reminder_count + 1;

            $record->update([
                'reminder_count'        => $newReminderCount,
                'last_reminder_sent_at' => now(),
            ]);

            // ================= AUDIT =================
            CustomFunctions::audit(
                module: 'PAF Request Information',
                action: 'REMINDER SENT',
                referenceId: $record->id,
                referenceTable: 'paf_request_information',
                oldValues: null,
                newValues: [
                    'reminder_count' => $newReminderCount,
                ],
                changedFields: ['reminder_count'],
                description: "Reminder #{$newReminderCount} sent ({$intervalDays} day cycle)"
            );

            $this->info("Reminder #{$newReminderCount} sent for PAF ID: {$record->paf_detail_id}");
        }

        $this->info('PAF Request Reminder Cron Completed');
    }
}
