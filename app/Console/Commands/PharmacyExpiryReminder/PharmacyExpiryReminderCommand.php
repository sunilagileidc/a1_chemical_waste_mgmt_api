<?php
namespace App\Console\Commands\PharmacyExpiryReminder;

use App\CustomClass\CustomFunctions;
use App\Mail\RegistrationRejectionMail;
use App\Models\EmailTemplate;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Mail;

class PharmacyExpiryReminderCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @author: Santhosha G
     *
     * @created-on: 28 Feb, 2026
     *
     * @var string
     */
    protected $signature = 'pharmacy:expiry-reminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pharmacy expiry reminder emails';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Pharmacy Expiry Reminder Cron Started');
        // Log::info('Pharmacy Expiry Reminder Cron Started');

        $today = Carbon::today();

        $users = $this->getUsersWithDrugs();

        $totalUsers = count($users);
        $mailCount  = 0;

        foreach ($users as $user) {

            $this->line("Processing User: {$user['full_name']} (ID: {$user['id']})");

            foreach ($user['drugs'] as $drug) {

                if (! empty($drug['duration'])) {

                    $messageData = [
                        'firstname'       => $user['full_name'],
                        'drug_name'       => $drug['drug_name'],
                        'start_date'      => $drug['start_date'],
                        'end_date'        => $drug['end_date'],
                        'expiry_duration' => $drug['duration'],
                    ];

                    $this->sendExpiryMail($user, $messageData);

                    $mailCount++;

                    $this->info("Mail sent → User: {$user['full_name']} | Drug: {$drug['drug_name']}");
                } else {
                    $this->line("Skipped Drug: {$drug['drug_name']} (No duration)");
                }
            }
        }

        $this->info("Completed. Total Users: {$totalUsers}, Total Mails Sent: {$mailCount}");
        // Log::info("Pharmacy Expiry Reminder Cron Completed", [
        //     'total_users' => $totalUsers,
        //     'mail_sent'   => $mailCount,
        // ]);
    }

    private function getUsersWithDrugs()
    {
        $today = Carbon::today();

        $users = User::select('id', 'name', 'lastname', 'email', 'role_id', 'email_subscription')
            ->with([
                'role:id,rolename',
                'prescriberMedications.drug:id,drug_name',
                'pharmacistMedications.drug:id,drug_name',
            ])
            ->whereHas('role', function ($q) {
                $q->whereIn('rolename', ['Prescriber', 'Pharmacist']);
            })
            ->get()
            ->map(function ($user) use ($today) {

                $medications = collect([]);

                if ($user->prescriberMedications) {
                    $medications = $medications->merge(
                        $user->prescriberMedications->map(function ($med) {
                            return [
                                'drug_name'  => $med->drug->drug_name ?? null,
                                'start_date' => $med->start_date,
                                'end_date'   => $med->end_date,
                                'expired'    => $med->expired,
                            ];
                        })
                    );
                }

                if ($user->pharmacistMedications) {
                    $medications = $medications->merge(
                        $user->pharmacistMedications->map(function ($med) {
                            return [
                                'drug_name'  => $med->drug->drug_name ?? null,
                                'start_date' => $med->start_date,
                                'end_date'   => $med->end_date,
                                'expired'    => $med->expired,
                            ];
                        })
                    );
                }

                $medications = $medications->map(function ($drug) use ($today) {

                    $expiry = Carbon::parse($drug['end_date']);
                    $days   = $today->diffInDays($expiry, false);

                    $duration = null;
                    if ($days == 90) {
                        $duration = "will expire in 3 months";
                    } elseif ($days == 30) {
                        $duration = "will expire in 1 month";
                    } elseif ($days == 7) {
                        $duration = "will expire in 1 week";
                    } elseif ($days == 1) {
                        $duration = "will expire tomorrow";
                    } elseif ($days == 0) {
                        $duration = "will expire today";
                    } elseif ($days < 0) {
                        $duration = "has expired";
                    }

                    $drug['duration'] = $duration;

                    return $drug;

                })->filter(function ($drug) {
                    return ! empty($drug['duration']);
                });

                return [
                    'id'                 => $user->id,
                    'full_name'          => $user->full_name,
                    'email'              => $user->email,
                    'email_subscription' => $user->email_subscription,
                    'role'               => $user->role->rolename ?? null,
                    'drugs'              => $medications->values(),
                ];

            });

        return $users;
    }

    private function sendExpiryMail($user, $drug)
    {
        try {

            $emailTemplate = EmailTemplate::where('template_name', 'Pharmacy Registration Expiry')->first();
            if (isset($emailTemplate)) {
                if (
                    $emailTemplate->is_mandatory === 1 ||
                    ($emailTemplate->is_mandatory === 0 && $user['email_subscription'] == 1)
                ) {

                    $userdata = [
                        'firstname'       => $user['full_name'],
                        'drug_name'       => $drug['drug_name'],
                        'start_date'      => $drug['start_date'],
                        'end_date'        => $drug['end_date'],
                        'expiry_duration' => $drug['expiry_duration'],
                    ];

                    $parsedSubject = CustomFunctions::EmailContentParser(
                        $emailTemplate->template_subject,
                        $userdata
                    );

                    $parsedContent = CustomFunctions::EmailContentParser(
                        $emailTemplate->template_body,
                        $userdata
                    );

                    $parsedSignature = CustomFunctions::EmailContentParser(
                        $emailTemplate->template_signature,
                        $userdata
                    );

                    Mail::to($user['email'])->queue(
                        new RegistrationRejectionMail(
                            $parsedSubject,
                            $parsedContent,
                            $parsedSignature,
                            null,
                            null
                        )
                    );
                }
                Log::info("Expiry mail sent to: " . $user['email'] . " for drug " . $drug['drug_name']);
            }

        } catch (\Exception $e) {

            Log::error('Expiry mail failed: ' . $e->getMessage());

        }
    }
}
