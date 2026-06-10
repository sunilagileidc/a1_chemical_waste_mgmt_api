<?php
namespace App\CustomClass;

use App\Models\Activitylog;
use App\Models\Audit;
use App\Models\NonConformanceRules;
use App\Models\PAFNonConformance;
use App\Models\Role;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CustomFunctions
{

    public static function EmailContentParser($content, $data)
    {
        $content = str_replace('{{ ', '{{', $content);
        $content = str_replace(' }}', '}}', $content);
        $parsed  = preg_replace_callback('/{{(.*?)}}/', function ($matches) use ($data) {
            list($shortCode, $index) = $matches;
            if (isset($data[$index])) {
                return $data[$index];
            } else {
                return '';
                //throw new Exception("Shortcode {$shortCode} not found in template id {$this->id}", 1);
            }
        }, $content);

        return $parsed;
    }

    public static function parseEmbeddedImageString($reqString, $folder_name)
    {
        $stt = $reqString; // string containing base64 encoded image files.
        preg_match('#data:image/(gif|png|jpeg);base64,([\w=+/]++)#', $stt, $x);
        while (isset($x[0])) {
            $imgdata      = base64_decode($x[0]);
            $info         = explode(";", explode("/", $x[0])[1])[0];
            $folderName   = $folder_name;
            $safeName     = Str::uuid() . '.png';
            $filewithpath = "storage/" . $folderName . '/' . $safeName;
            if (config('filesystems.default') == 's3') {
                Storage::disk('s3')->put($filewithpath, file_get_contents($x[0]));
                $stt = str_replace($x[0], Storage::disk('s3')->url($filewithpath), $stt);
            } else {
                Storage::disk('public')->put($folderName . '/' . $safeName, file_get_contents($x[0]));
                $stt = str_replace($x[0], asset('/' . $filewithpath), $stt);
            }
            preg_match('#data:image/(gif|png|jpeg);base64,([\w=+/]++)#', $stt, $x);
        }
        return $stt;
    }

    public static function addActivitylog($userid, $logtype, $title, $description, $createdby, $creationdate)
    {
        try {
            $activity_log = Activitylog::create([
                'user_id'       => $userid,
                'log_type'      => $logtype,
                'title'         => $title,
                'description'   => $description,
                'created_by'    => $createdby,
                'creation_date' => $creationdate,
            ]);

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'activity_log' => $activity_log]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }

    }

    public static function updateSlug($id, $name, $table)
    {
        try {
            if ($id > 0 && ($name != '' || $name != null) && ($table != '' || $table != null)) {

                $slug_string = $name . ' ' . $id;

                DB::table($table)
                    ->where('id', $id)
                    ->update(['slug' => Str::slug($slug_string, '-')]);
            }

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.slugupdated')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }

    }

    public static function getRoleIdByName($rolename)
    {
        try {

            if (! $rolename) {
                return null;
            }

            $role = Role::where('rolename', $rolename)->first();

            if ($role) {
                return $role->id;
            }

            return null;

        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * @function: to create a audit details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Mar, 2026
     *
     * @updated-on: N/A
     */
    public static function audit(
        string $module,
        string $action,
        ?int $userId = null,
        ?int $referenceId = null,
        ?string $referenceTable = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?array $changedFields = null,
        string $status = 'SUCCESS',
        ?string $description = null
    ) {
        try {

            Audit::create([
                'user_id'         => $userId ?? Auth::id(),
                'module'          => $module,
                'action'          => $action,
                'reference_id'    => $referenceId,
                'reference_table' => $referenceTable,
                'old_values'      => $oldValues,
                'new_values'      => $newValues,
                'changed_fields'  => $changedFields,
                'ip_address'      => Request::ip(),
                'user_agent'      => Request::userAgent(),
                'url'             => Request::fullUrl(),
                'status'          => $status,
                'description'     => $description,
            ]);

        } catch (\Exception $e) {
            \Log::error('Audit log failed: ' . $e->getMessage());
        }
    }


    /**
     * Create non conformance
     * @author: Stalvin M
     *
     * @created-on: 27 Apr, 2026
     *
     * @updated-on: N/A
     * @param $pafDetailsId, $type, $userId
     * @return string
     */
    public static function createNonConformance($pafDetailsId, $type, $userId)
    {
        try {
            $rule = NonConformanceRules::where('conformance_type', $type)
                ->where('status', 1)
                ->first();

            if (! $rule) {
                \Log::warning('NonConformance rule not found', [
                    'type'           => $type,
                    'paf_details_id' => $pafDetailsId,
                ]);
                return false;
            }

            PAFNonConformance::create([
                'paf_details_id' => $pafDetailsId,
                'note'           => $rule->description,
                'type'           => $type,
                'created_by'     => $userId,
                'updated_by'     => $userId,
            ]);

            return true;

        } catch (\Exception $e) {

            \Log::error('Failed to create non-conformance', [
                'type'           => $type,
                'paf_details_id' => $pafDetailsId,
                'user_id'        => $userId,
                'error'          => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * To get non conformance message based on type
     * @author: Stalvin M
     *
     * @created-on: 27 Apr, 2026
     *
     * @updated-on: N/A
     * @param string $type
     * @return string
     */
    public static function getNonConformanceMessage($type)
    {
        try {
            $rule = NonConformanceRules::where('conformance_type', $type)
                ->where('status', 1)
                ->first();

            if (! $rule) {
                \Log::warning('NonConformance rule not found', [
                    'type' => $type,
                ]);
                return null;
            }

            return $rule->description;

        } catch (\Exception $e) {

            \Log::error('Failed to fetch non-conformance message', [
                'type'  => $type,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public static function applyNonConformanceRules($oldPaf, $newPaf, $request, $userId)
    {
        try {

            // -----------------------------------------
            // 1 Get all ACTIVE rules
            // -----------------------------------------
            $rules = NonConformanceRules::where('status', 1)
                ->get()
                ->keyBy('conformance_type');

            // -----------------------------------------
            // 2 Define dynamic rules
            // -----------------------------------------
            $ruleConditions = [

                'WCBP_NEG_PREG_INVALID_RANGE' => function () use ($request, $newPaf) {

                    if ($newPaf->patient_category !== 'WCBP') {
                        return false;
                    }

                    $negPregDate = $request->last_negative_preg_date ?? null;

                    if (! $negPregDate) {
                        return true;
                    }

                    $negDate       = Carbon::parse($negPregDate)->startOfDay();
                    $referenceDate = ! empty($oldPaf['declaration_date'])
                        ? Carbon::parse($oldPaf['declaration_date'])->startOfDay()
                        : Carbon::today();

                    $threeDaysAgo = $referenceDate->copy()->subDays(3);

                    return $negDate->lt($threeDaysAgo) || $negDate->gt($referenceDate);
                },

                'WCBP_MAX_1_CYCLE'            => function () use ($request, $newPaf) {

                    if ($newPaf->patient_category !== 'WCBP') {
                        return false;
                    }

                    preg_match('/\d+/', $request->cycles, $matches);
                    $cycles = isset($matches[0]) ? (int) $matches[0] : 0;

                    return $cycles > 1;
                },

                'THALIDOMIDE_DOSAGE_RULE'     => function () use ($request, $newPaf) {

                    // Apply only for relevant patients (optional: both WCBP & WNCBP)
                    if (! in_array($newPaf->patient_category, ['WCBP', 'WNCBP'])) {
                        return false;
                    }

                    // Get drug name
                    $drugName = self::getDrugName($newPaf->drug_id);

                    if (! in_array($drugName, ['50mg - Thalidomide', '100mg - Thalidomide Tablet'])) {
                        return false; // not applicable
                    }

                    // Extract supply weeks
                    preg_match('/\d+/', $request->total_supply ?? $request->supply_weeks ?? '', $weekMatches);
                    $supplyWeeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

                    if ($supplyWeeks <= 0) {
                        return false;
                    }

                    $expectedCapsules = $supplyWeeks * 7;

                    // Loop dosage
                    foreach ($request->drug_cycles ?? $request->dosage ?? [] as $dose) {

                        preg_match('/\d+/', $dose['cap_per_cycle'] ?? $dose['capsules'] ?? '', $capsuleMatches);
                        $capsules = isset($capsuleMatches[0]) ? (int) $capsuleMatches[0] : 0;

                        if ($capsules != $expectedCapsules) {
                            return true; // ❗ rule violated → create NC
                        }
                    }

                    return false;
                },

                'RISK_NOT_CONFIRMED'          => function () use ($request) {

                    $riskConfirmed = $request->risk_confirmed ?? 0;

                    return (int) $riskConfirmed === 0;
                },

            ];

            $dynamicTypes = array_keys($ruleConditions);

            // -----------------------------------------
            // 3 Copy OLD NCs (SKIP dynamic types)
            // -----------------------------------------
            $oldNCs = PAFNonConformance::where('paf_details_id', $oldPaf->id)->get();

            foreach ($oldNCs as $nc) {

                if (in_array($nc->type, $dynamicTypes)) {
                    continue;
                }
                PAFNonConformance::create([
                    'paf_details_id' => $newPaf->id,
                    'type'           => $nc->type,
                    'note'           => $nc->note,
                    'created_by'     => $userId,
                    'updated_by'     => $userId,
                ]);
            }

            // -----------------------------------------
            // 4 Apply dynamic rules fresh
            // -----------------------------------------
            foreach ($ruleConditions as $type => $condition) {

                if (! isset($rules[$type])) {
                    continue;
                }

                if ($condition()) {

                    PAFNonConformance::create([
                        'paf_details_id' => $newPaf->id,
                        'type'           => $rules[$type]->conformance_type,
                        'note'           => $rules[$type]->description,
                        'created_by'     => $userId,
                        'updated_by'     => $userId,
                    ]);
                }
            }

        } catch (\Exception $e) {

            \Log::error('Rule engine failed', [
                'old_paf_id' => $oldPaf->id ?? null,
                'new_paf_id' => $newPaf->id ?? null,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    public static function copyNonConformances($oldPafDetailsId, $newPafDetailsId, $userId)
    {
        try {
            $oldNCs = PAFNonConformance::where('paf_details_id', $oldPafDetailsId)->get();

            if ($oldNCs->isEmpty()) {
                return; // nothing to copy
            }

            foreach ($oldNCs as $nc) {
                PAFNonConformance::create([
                    'paf_details_id' => $newPafDetailsId,
                    'note'           => $nc->note,
                    'type'           => $nc->type ?? null,
                    'created_by'     => $userId,
                    'updated_by'     => $userId,
                ]);
            }

        } catch (\Exception $e) {
            \Log::error('Failed to copy non-conformances', [
                'old_paf_details_id' => $oldPafDetailsId,
                'new_paf_details_id' => $newPafDetailsId,
                'error'              => $e->getMessage(),
            ]);
        }
    }

}
