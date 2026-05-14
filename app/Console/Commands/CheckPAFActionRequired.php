<?php
namespace App\Console\Commands;

use App\CustomClass\CustomFunctions;
use App\Models\PafDetails;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckPAFActionRequired extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-paf-action-required';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark PAF as Action Required if time expired and trigger notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {

            $systemUserId = 1;
            $today        = Carbon::now();

            PafDetails::where('status', '!=', 'Dispensed')
                ->whereNotIn('status', ['Completed', 'Cancelled'])
                ->whereRaw('version = (
                SELECT MAX(p2.version)
                FROM paf_details p2
                WHERE
                    (p2.parent_id = paf_details.parent_id
                    OR (p2.parent_id IS NULL AND paf_details.parent_id IS NULL))
            )')
                ->chunk(100, function ($records) use ($today, $systemUserId) {

                    foreach ($records as $record) {

                        $baseDate = $record->declaration_date
                            ? Carbon::parse($record->declaration_date)
                            : Carbon::parse($record->created_at);
                        $daysPassed = $baseDate->diffInDays($today);

                        $limitDays = 0;
                        $notify    = false;

                        // ================= CATEGORY LOGIC =================
                        if ($record->patient_category === 'WCBP') {

                            $limitDays = 7;

                            // Notify only ONCE at day 5
                            if ($daysPassed === 5) {
                                $notify = true;
                            }

                        } elseif (in_array($record->patient_category, ['M', 'WNCBP'])) {

                            $limitDays = 84;

                            // Notify every 14 days (with buffer)
                            if ($daysPassed >= 14 && ($daysPassed % 14 === 0 || $daysPassed % 14 === 1)) {
                                $notify = true;
                            }
                        }

                        // ================= STATUS UPDATE =================
                        if ($daysPassed >= $limitDays && $record->status !== 'Action Required') {

                            $oldStatus = $record->status;

                            $record->update([
                                'status'     => 'Action Required',
                                'updated_at' => now(),
                            ]);

                            // Detailed Description
                            $description = "PAF ID: " . $record->id .
                            ", Patient ID: " . ($record->patient_id ?? 'N/A') .
                            ", Category: " . ($record->patient_category ?? 'N/A') .
                            ", Created Date: " . $baseDate->format('Y-m-d') .
                                ", Days Passed: " . $daysPassed .
                                ", Limit: " . $limitDays . " days" .
                                ", Status changed from '" . $oldStatus . "' to 'Action Required'.";

                            // Audit
                            CustomFunctions::audit(
                                module: 'PAF',
                                action: 'AUTO ACTION REQUIRED',
                                userId: $systemUserId,
                                referenceId: $record->id,
                                referenceTable: 'paf_details',
                                oldValues: ['status' => $oldStatus],
                                newValues: ['status' => 'Action Required'],
                                changedFields: ['status'],
                                description: $description
                            );
                        }

                        // ================= NOTIFICATION =================
                        if ($notify) {

                            $notifyDescription = "Notification triggered for PAF ID: " . $record->id .
                                ", Patient ID: " . ($record->patient_id ?? 'N/A') .
                                ", Category: " . ($record->patient_category ?? 'N/A') .
                                ", Days Passed: " . $daysPassed . ".";

                            CustomFunctions::audit(
                                module: 'PAF Notification',
                                action: 'AUTO NOTIFY',
                                referenceId: $record->id,
                                userId: $systemUserId,
                                referenceTable: 'paf_details',
                                oldValues: [],
                                newValues: [],
                                changedFields: [],
                                description: $notifyDescription
                            );
                        }
                    }
                });

            $this->info('PAF Action Required check completed successfully.');

        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
        }
    }
}
