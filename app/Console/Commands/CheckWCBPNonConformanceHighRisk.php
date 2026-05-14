<?php
namespace App\Console\Commands;

use App\CustomClass\CustomFunctions;
use App\Models\PafDetails;
use App\Models\PAFNonConformance;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckWCBPNonConformanceHighRisk extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wcbp:nonconformance-highrisk';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark WCBP non-conformance records as High Risk if older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            DB::beginTransaction();

            $userId = 1; // change if you have system user

            $date = Carbon::now()->subDays(7);

            // Fetch only latest version records
            $records = PafDetails::where('patient_category', 'WCBP')
                ->whereDate('declaration_date', '<=', $date)
                ->where('status', '!=', 'Dispensed')
                ->whereRaw('version = (
                    SELECT MAX(p2.version)
                    FROM paf_details p2
                    WHERE
                        (p2.parent_id = paf_details.parent_id
                        OR (p2.parent_id IS NULL AND paf_details.parent_id IS NULL))
                )')
                ->get();

            foreach ($records as $record) {

                // Skip if already High Risk (optional safety)
                if ($record->risk_level === 'High Risk') {
                    continue;
                }

                $oldRisk = $record->risk_level;

                // Update risk level
                $record->update([
                    'risk_level' => 'High Risk',
                    'updated_at' => now(),
                ]);

                // Create Non-Conformance Entry
                PAFNonConformance::create([
                    'paf_details_id' => $record->id,
                    'note'           => 'Auto-classified as High Risk: WCBP record exceeds 7 calendar days from creation date.',
                    'created_by'     => $userId,
                    'updated_by'     => $userId,
                ]);

                // Audit Log
                CustomFunctions::audit(
                    module: 'PAF Non-Conformance',
                    action: 'AUTO HIGH RISK',
                    referenceId: $record->id,
                    referenceTable: 'paf_details',
                    oldValues: ['risk_level' => $oldRisk],
                    newValues: ['risk_level' => 'High Risk'],
                    changedFields: ['risk_level'],
                    description: 'Auto marked as High Risk (WCBP > 7 days)'
                );
            }

            DB::commit();

            $this->info('WCBP Non-Conformance High Risk check completed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();

            $this->error('Error: ' . $e->getMessage());
        }
    }
}
