<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
use App\Models\DrugCapsules;
use App\Models\DrugCycles;
use App\Models\DrugIndications;
use App\Models\DrugMarketingHolders;
use App\Models\Drugs;
use App\Models\EmailTemplate;
use App\Models\Institutions;
use App\Models\PharmacistDetails;
use App\Models\PharmacistMedication;
use App\Models\PrescriberDetails;
use App\Models\PrescriberMedication;
use App\Models\Strengths;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Log;

class DrugsApiController extends Controller
{
    /**
     * @function: to fetch drugs details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 18 Feb 2026
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $drugs = Drugs::with('drugStrength', 'cycles', 'capsules')->orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'drug' => $drugs]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to store drug details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 18 Feb 2026
     *
     * @updated-on: 29 Apr 2026
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'drug_form' => 'required',
            'drug_name' => 'required',
            'prescriber_confirmation_text' => 'required',
            'sequence' => 'required',
            'pharmacist_confirmation_text' => 'required',
            'capsule_strength' => 'array',
            'capsules' => 'array',
            'cycles' => 'array',
            'marketing_holders' => 'array',
            'indications' => 'array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {
            DB::beginTransaction();

            // ================= UPDATE =================
            if ($request->id > 0) {

                $drug = Drugs::findOrFail($request->id);

                $oldData = $drug->load([
                    'drugStrength',
                    'indications',
                    'marketing_holders',
                    'capsules',
                    'cycles',
                ])->toArray();

                $drug->update([
                    'drug_name' => $request->drug_name,
                    'drug_form' => $request->drug_form,
                    'validity' => $request->validity,
                    'status' => $request->status,
                    'prescriber_confirmation_text' => $request->prescriber_confirmation_text,
                    'sequence' => $request->sequence,
                    'pharmacist_confirmation_text' => $request->pharmacist_confirmation_text,
                    'updated_by' => Auth::id(),
                ]);

                // delete old strengths
                Strengths::where('drug_id', $drug->id)->delete();

                // insert new strengths
                if (!empty($request->drug_strength)) {

                    $strengthsData = [];

                    foreach ($request->drug_strength as $strength) {
                        $strengthsData[] = [
                            'drug_id' => $drug->id,
                            'capsule_strength' => $strength,
                            'status' => 1,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    Strengths::insert($strengthsData);
                }

                //1 delete old DrugIndications
                DrugIndications::where('drug_id', $drug->id)->delete();

                // insert new strengths
                if (!empty($request->indications)) {
                    $indicationsData = [];
                    foreach ($request->indications as $indications) {
                        $indicationsData[] = [
                            'drug_id' => $drug->id,
                            'indication_id' => $indications,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DrugIndications::insert($indicationsData);
                }

                //2 delete old DrugMarketingHolders
                DrugMarketingHolders::where('drug_id', $drug->id)->delete();

                // insert new DrugMarketingHolders
                if (!empty($request->marketing_holders)) {
                    $imarketingHoldersData = [];
                    foreach ($request->marketing_holders as $marketing_holders) {
                        $imarketingHoldersData[] = [
                            'drug_id' => $drug->id,
                            'marketing_holder_id' => $marketing_holders,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DrugMarketingHolders::insert($imarketingHoldersData);
                }

                //3 delete old DrugCapsules
                DrugCapsules::where('drug_id', $drug->id)->delete();

                // insert new DrugCapsules
                if (!empty($request->capsules)) {
                    $capsulesData = [];
                    foreach ($request->capsules as $capsules) {
                        $capsulesData[] = [
                            'drug_id' => $drug->id,
                            'no_of_capsules' => $capsules,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DrugCapsules::insert($capsulesData);
                }

                //4 delete old DrugCycles
                DrugCycles::where('drug_id', $drug->id)->delete();

                // insert new DrugCycles
                if (!empty($request->cycles)) {
                    $drugCyclesData = [];
                    foreach ($request->cycles as $cycles) {
                        $drugCyclesData[] = [
                            'drug_id' => $drug->id,
                            'no_of_cycle_weeks' => $cycles,
                            'created_by' => Auth::id(),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    DrugCycles::insert($drugCyclesData);
                }

                $newData = $drug->fresh()->load([
                    'drugStrength',
                    'indications',
                    'marketing_holders',
                    'capsules',
                    'cycles',
                ])->toArray();

                CustomFunctions::audit(
                    module: 'Drugs',
                    action: 'UPDATE',
                    referenceId: $drug->id,
                    referenceTable: 'drugs',
                    oldValues: $oldData,
                    newValues: $newData,
                    description: "Drug '{$drug->drug_name}' details updated including strengths, indications, marketing holders, capsules, and cycles."
                );

                DB::commit();
                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                    'drug' => $drug,
                ]);
            }
            // Check duplicate drug name
            $exists = Drugs::where('drug_name', $request->drug_name)
                ->where('id', '!=', $request->id)
                ->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'E',
                    'message' => trans('returnmessage.already_exists'),
                ]);
            }
            // ================= CREATE =================
            $drug = Drugs::create([
                'drug_name' => $request->drug_name,
                'drug_form' => $request->drug_form,
                'validity' => $request->validity,
                'status' => $request->status,
                'prescriber_confirmation_text' => $request->prescriber_confirmation_text,
                'pharmacist_confirmation_text' => $request->pharmacist_confirmation_text,
                'sequence' => $request->sequence,
                'created_by' => Auth::id(),
            ]);

            if (!empty($request->drug_strength)) {

                $strengthsData = [];

                foreach ($request->drug_strength as $strength) {
                    $strengthsData[] = [
                        'drug_id' => $drug->id,
                        'capsule_strength' => $strength,
                        'status' => 1,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                Strengths::insert($strengthsData);
            }

            //1 insert new strengths
            if (!empty($request->indications)) {
                $indicationsData = [];
                foreach ($request->indications as $indications) {
                    $indicationsData[] = [
                        'drug_id' => $drug->id,
                        'indication_id' => $indications,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DrugIndications::insert($indicationsData);
            }

            //2 insert new DrugMarketingHolders
            if (!empty($request->marketing_holders)) {
                $imarketingHoldersData = [];
                foreach ($request->marketing_holders as $marketing_holders) {
                    $imarketingHoldersData[] = [
                        'drug_id' => $drug->id,
                        'marketing_holder_id' => $marketing_holders,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DrugMarketingHolders::insert($imarketingHoldersData);
            }

            //3 insert new DrugCapsules
            if (!empty($request->capsules)) {
                $capsulesData = [];
                foreach ($request->capsules as $capsules) {
                    $capsulesData[] = [
                        'drug_id' => $drug->id,
                        'no_of_capsules' => $capsules,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DrugCapsules::insert($capsulesData);
            }

            //4 insert new DrugCycles
            if (!empty($request->cycles)) {
                $drugCyclesData = [];
                foreach ($request->cycles as $cycles) {
                    $drugCyclesData[] = [
                        'drug_id' => $drug->id,
                        'no_of_cycle_weeks' => $cycles,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                DrugCycles::insert($drugCyclesData);
            }
            $newData = $drug->load([
                'drugStrength',
                'indications',
                'marketing_holders',
                'capsules',
                'cycles',
            ])->toArray();

            CustomFunctions::audit(
                module: 'Drugs',
                action: 'CREATE',
                referenceId: $drug->id,
                referenceTable: 'drugs',
                newValues: $newData,
                description: "New drug '{$drug->drug_name}' created with strengths, indications, marketing holders, capsules, and cycles."
            );
            DB::commit();
            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.createdsuccessfully'),
                'drug' => $drug,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::info("Exception=>" . $e);
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to edit Drug details.
     *
     * @author: Raghavendra kumar S
     *
     * @created-on: 19 Feb , 2026
     *
     * @updated-on: N/A
     */
    public function editDrug($slug)
    {
        try {

            $drug = Drugs::with('drugStrength', 'indications', 'cycles', 'capsules', 'marketing_holders')
                ->where('slug', $slug)
                ->firstOrFail();

            $drug->setRelation(
                'marketing_holders',
                $drug->marketing_holders->pluck('marketing_holder_id')->values()
            );

            $drug->setRelation(
                'indications',
                $drug->indications->pluck('indication_id')->values()
            );

            $drug->setRelation(
                'cycles',
                $drug->cycles->pluck('no_of_cycle_weeks')->values()
            );

            $drug->setRelation(
                'capsules',
                $drug->capsules->pluck('no_of_capsules')->values()
            );

            $drug->drug_strength = $drug->drugStrength
                ->pluck('capsule_strength')
                ->values();
            unset($drug->drugStrength);

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.data_return'),
                'drug' => $drug,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to delete drug.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 19 Feb 2026
     *
     * @updated-on: 29 Apr 2026
     */
    public function deleteDrug($id)
    {
        try {
            DB::beginTransaction();

            $drug = Drugs::findOrFail($id);

            $oldData = $drug->toArray(); // capture before delete

            $drug->delete();

            CustomFunctions::audit(
                module: 'Drugs',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'drugs',
                oldValues: $oldData,
                description: "Drug '{$oldData['drug_name']}' deleted"
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.deletedsuccessfully'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to fetch active drugs.
     *
     * @author: Stalvin M
     *
     * @created-on: 19 Feb 2026
     *
     * @updated-on: N/A
     */
    public function fetchActiveDrugs()
    {
        try {
            $drugs = Drugs::where("status", 1)->orderBy("sequence", "asc")->get(["id", "drug_name", "id as drug_id"]);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'drugs' => $drugs]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to Update the drug status.
     *
     * @author: Santhosha G
     *
     * @created-on: 23 Feb 2026
     *
     * @updated-on: 29 Apr 2026
     */
    public function updateDrugStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $drug = Drugs::findOrFail($request->id);

            $oldData = $drug->toArray();

            $drug->status = $drug->status == 1 ? 0 : 1;
            $drug->save();

            $newData = $drug->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Drugs',
                action: 'STATUS UPDATE',
                referenceId: $drug->id,
                referenceTable: 'drugs',
                oldValues: $oldData,
                newValues: $newData,
                description: "Drug '{$drug->drug_name}' status changed from " .
                ($oldData['status'] == 1 ? 'Active' : 'Inactive') .
                " to " .
                ($newData['status'] == 1 ? 'Active' : 'Inactive')
            );
            DB::commit();
            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.updatedsuccessfully'),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to Update the drug status.
     *
     * @author: Raghavendra Kumar
     *
     * @created-on: 27 Feb 2026
     *
     * @updated-on: N/A
     */

    public function updateDrugs(Request $request)
    {

        DB::beginTransaction();

        try {
            $moduleName = $request->role === 'Prescriber'
            ? 'Prescriber Medication'
            : 'Pharmacist Medication';

            $referenceTable = $request->role === 'Prescriber'
            ? 'prescriber_medications'
            : 'pharmacist_medications';

            $today = Carbon::today();
            // Get drug validity
            $drug = Drugs::select('drug_name', 'validity')
                ->where('id', $request->drug_id)
                ->first();

            if (!$drug) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Drug not found',
                ]);
            }

            $drugName = $drug->drug_name;
            $validityMonths = (int) $drug->validity;
            $startDate = $today;
            $endDate = $today->copy()->addMonths($validityMonths);
            $isNewDrug = $request->newDrug ?? false;

            // =========================================================
            // PHARMACIST
            // =========================================================
            if ($request->role == 'Pharmacist') {

                $hospitalName = Institutions::where('id', $request->pharmacist_id)
                    ->value('name');

                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'pharmacist_id' => 'required',
                    'drug_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'E',
                        'message' => $validator->errors()->first(),
                    ]);
                }

                // =========================
                // NEW DRUG (NO VERSION)
                // =========================

                if ($isNewDrug) {
                    $pharmacist_id = PharmacistDetails::where('institution_id', $request->pharmacist_id)
                        ->value('id');

                    $newRecord = PharmacistMedication::create([
                        'user_id' => $request->user_id,
                        'pharmacist_id' => $pharmacist_id,
                        'drug_id' => $request->drug_id,
                        'is_check' => 1,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'version' => 1, // first version
                        'expired' => 0,
                        'created_by' => Auth::id(),
                    ]);

                    CustomFunctions::audit(
                        module: $moduleName,
                        action: 'CREATE',
                        referenceId: $newRecord->id,
                        referenceTable: $referenceTable,
                        oldValues: null,
                        newValues: $newRecord->toArray(),
                        description: "Drug '{$drugName}' registered for Pharmacist at '{$hospitalName}'"
                    );

                } else {

                    $oldRecords = PharmacistMedication::where('user_id', $request->user_id)
                        ->where('drug_id', $request->drug_id)
                        ->where('pharmacist_id', $request->pharmacist_id)
                        ->where('expired', 0)
                        ->get()
                        ->toArray();
                    // =========================
                    // RE-REGISTER FLOW
                    // =========================

                    $latest = PharmacistMedication::where('user_id', $request->user_id)
                        ->where('drug_id', $request->drug_id)
                        ->where('pharmacist_id', $request->pharmacist_id)
                        ->orderByDesc('version')
                        ->first();

                    $newVersion = $latest ? $latest->version + 1 : 1;

                    // Expire existing active record
                    PharmacistMedication::where('user_id', $request->user_id)
                        ->where('drug_id', $request->drug_id)
                        ->where('pharmacist_id', $request->pharmacist_id)
                        ->where('expired', 0)
                        ->update([
                            'expired' => 1,
                            'expiry_reason' => 'expired due to re-registration',
                            'updated_at' => now(),
                            'updated_by' => Auth::id(),
                        ]);

                    // Insert new version
                    $newRecord = PharmacistMedication::create([
                        'user_id' => $request->user_id,
                        'pharmacist_id' => $request->pharmacist_id,
                        'drug_id' => $request->drug_id,
                        'is_check' => 1,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'version' => $newVersion,
                        'expired' => 0,
                        'created_by' => $request->user_id,
                    ]);

                    CustomFunctions::audit(
                        module: $moduleName,
                        action: 'UPDATE',
                        referenceId: $newRecord->id,
                        referenceTable: $referenceTable,
                        oldValues: !empty($oldRecords) ? $oldRecords : null,
                        newValues: $newRecord->toArray(),
                        description: "Drug '{$drugName}' re-registered for Pharmacist at '{$hospitalName}' (version {$newVersion})");
                }
            }

            // =========================================================
            // PRESCRIBER
            // =========================================================
            if ($request->role == 'Prescriber') {
                $hospitalName = Institutions::where('id', $request->prescriber_id)
                    ->value('name');

                $validator = Validator::make($request->all(), [
                    'user_id' => 'required',
                    'prescriber_id' => 'required',
                    'drug_id' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'E',
                        'message' => $validator->errors()->first(),
                    ]);
                }

                // =========================
                // NEW DRUG
                // =========================
                if ($isNewDrug) {
                    $presciber_id = PrescriberDetails::where('institution_id', $request->prescriber_id)
                        ->value('id');
                    $newRecord = PrescriberMedication::create([
                        'user_id' => $request->user_id,
                        'prescriber_id' => $presciber_id,
                        'drug_id' => $request->drug_id,
                        'is_check' => 1,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'version' => 1,
                        'expired' => 0,
                        'created_by' => Auth::id(),
                    ]);

                    CustomFunctions::audit(
                        module: $moduleName,
                        action: 'CREATE',
                        referenceId: $newRecord->id,
                        referenceTable: $referenceTable,
                        oldValues: null,
                        newValues: $newRecord->toArray(),
                        description: "Drug '{$drugName}' registered for Prescriber at '{$hospitalName}'"
                    );

                } else {

                    //RE-REGISTER FLOW

                    $oldRecords = PrescriberMedication::where('user_id', $request->user_id)
                        ->where('drug_id', $request->drug_id)
                        ->where('prescriber_id', $request->prescriber_id)
                        ->where('expired', 0)
                        ->get()
                        ->toArray();

                    $latest = PrescriberMedication::where('user_id', $request->user_id)
                        ->where('drug_id', $request->drug_id)
                        ->where('prescriber_id', $request->prescriber_id)
                        ->orderByDesc('version')
                        ->first();

                    $newVersion = $latest ? $latest->version + 1 : 1;

                    // Expire old
                    PrescriberMedication::where('user_id', $request->user_id)
                        ->where('drug_id', $request->drug_id)
                        ->where('prescriber_id', $request->prescriber_id)
                        ->where('expired', 0)
                        ->update([
                            'expired' => 1,
                            'expiry_reason' => 'expired due to re-registration',
                            'updated_at' => now(),
                            'updated_by' => Auth::id(),
                        ]);

                    // Insert new
                    $newRecord = PrescriberMedication::create([
                        'user_id' => $request->user_id,
                        'prescriber_id' => $request->prescriber_id,
                        'drug_id' => $request->drug_id,
                        'is_check' => 1,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'version' => $newVersion,
                        'expired' => 0,
                        'created_by' => $request->user_id,
                    ]);

                    CustomFunctions::audit(
                        module: $moduleName,
                        action: 'UPDATE',
                        referenceId: $newRecord->id,
                        referenceTable: $referenceTable,
                        oldValues: !empty($oldRecords) ? $oldRecords : null,
                        newValues: $newRecord->toArray(),
                        description: "Drug '{$drugName}' re-registered for Prescriber at '{$hospitalName}' (version {$newVersion})"
                    );
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => $isNewDrug
                ? 'Drug registered successfully.'
                : 'Drug re-registered successfully.',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to Update the drug status.
     *
     * @author: Santhosha G
     *
     * @created-on: 21 Mar 2026
     *
     * @updated-on: N/A
     */
    public function forceToReRegister(Request $request)
    {
        DB::beginTransaction();

        try {

            // Validate first
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'drug_id' => 'required|exists:drugs,id',
            ]);

            if ($request->pharmacist_id) {
                $validator->addRules(['pharmacist_id' => 'required']);
            }

            if ($request->prescriber_id) {
                $validator->addRules(['prescriber_id' => 'required']);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'E',
                    'message' => $validator->errors()->first(),
                ]);
            }

            // Get Data
            $drug = Drugs::select('validity', 'drug_name')
                ->where('id', $request->drug_id)
                ->first();

            $user = User::find($request->user_id);

            if (!$drug || !$user) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User or Drug not found',
                ]);
            }

            $reason = 'Admin Forced Re-registration';

            // ================= DETERMINE ROLE =================
            $type = null;
            $model = null;
            $table = null;
            $whereClause = [];

            if ($request->pharmacist_id) {
                $type = 'Pharmacist';
                $model = PharmacistMedication::class;
                $table = 'pharmacist_medications';

                $whereClause = [
                    'user_id' => $request->user_id,
                    'drug_id' => $request->drug_id,
                    'pharmacist_id' => $request->pharmacist_id,
                    'expired' => 0,
                ];
            }

            if ($request->prescriber_id) {
                $type = 'Prescriber';
                $model = PrescriberMedication::class;
                $table = 'prescriber_medications';

                $whereClause = [
                    'user_id' => $request->user_id,
                    'drug_id' => $request->drug_id,
                    'prescriber_id' => $request->prescriber_id,
                    'expired' => 0,
                ];
            }

            // ================= CAPTURE OLD DATA =================
            $oldMedications = $model::where($whereClause)->get()->toArray();

            // ================= UPDATE (EXPIRE) =================
            $model::where($whereClause)->update([
                'expired' => 1,
                'expiry_reason' => $reason,
                'updated_at' => now(),
                'updated_by' => Auth::id(),
            ]);

            // ================= PREPARE NEW DATA =================
            $newMedications = [];

            if (!empty($oldMedications)) {
                $newMedications = collect($oldMedications)->map(function ($item) use ($reason) {
                    $item['expired'] = 1;
                    $item['expiry_reason'] = $reason;
                    return $item;
                })->toArray();
            }

            // ================= AUDIT =================
            if (!empty($oldMedications)) {

                $moduleName = $type . ' Registration';

                CustomFunctions::audit(
                    module: $moduleName,
                    action: 'UPDATE',
                    referenceId: $request->user_id,
                    referenceTable: $table,
                    oldValues: $oldMedications,
                    newValues: $newMedications,
                    description: 'Admin forced re-registration for drug: ' . $drug->drug_name
                );
            }

            // ================= EMAIL =================
            $emailTemplate = EmailTemplate::where('template_name', 'Forced Re-registration Notice')->first();

            if ($emailTemplate && $user) {

                if (
                    $emailTemplate->is_mandatory === 1 ||
                    ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                ) {

                    $userdata = [
                        'firstname' => $user->full_name,
                        'drug_name' => $drug->drug_name,
                        'reason' => $reason,
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

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Drug expired and re-registration email sent successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to Update the drug status.
     *
     * @author: Santhosha G
     *
     * @created-on: 21 Mar 2026
     *
     * @updated-on: N/A
     */
    public function forceToReRegisterSelectedDrug(Request $request)
    {
        DB::beginTransaction();

        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|exists:users,id',
                'drug_ids' => 'required|array',
                'drug_ids.*' => 'exists:drugs,id',
            ]);

            if ($request->pharmacist_id) {
                $validator->addRules(['pharmacist_id' => 'required']);
            }

            if ($request->prescriber_id) {
                $validator->addRules(['prescriber_id' => 'required']);
            }

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'E',
                    'message' => $validator->errors()->first(),
                ]);
            }

            $user = User::find($request->user_id);

            if (!$user) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            $reason = 'Admin Forced Re-registration';

            // ================= DETERMINE TYPE =================
            $type = null;
            $model = null;
            $table = null;
            $baseWhere = [];

            if ($request->pharmacist_id) {
                $type = 'Pharmacist';
                $model = PharmacistMedication::class;
                $table = 'pharmacist_medications';

                $baseWhere = [
                    'user_id' => $request->user_id,
                    'pharmacist_id' => $request->pharmacist_id,
                    'expired' => 0,
                ];
            }

            if ($request->prescriber_id) {
                $type = 'Prescriber';
                $model = PrescriberMedication::class;
                $table = 'prescriber_medications';

                $baseWhere = [
                    'user_id' => $request->user_id,
                    'prescriber_id' => $request->prescriber_id,
                    'expired' => 0,
                ];
            }

            // ================= GET DRUGS =================
            $drugs = Drugs::whereIn('id', $request->drug_ids)->get();

            $allOldMedications = [];
            $allNewMedications = [];
            $drugNames = [];

            foreach ($drugs as $drug) {

                $drugNames[] = $drug->drug_name;

                $whereClause = array_merge($baseWhere, [
                    'drug_id' => $drug->id,
                ]);

                // ===== OLD DATA =====
                $oldMedications = $model::where($whereClause)->get()->toArray();

                if (!empty($oldMedications)) {

                    // ===== UPDATE =====
                    $model::where($whereClause)->update([
                        'expired' => 1,
                        'expiry_reason' => $reason,
                        'updated_at' => now(),
                        'updated_by' => Auth::id(),
                    ]);

                    // ===== NEW DATA =====
                    $newMedications = collect($oldMedications)->map(function ($item) use ($reason) {
                        $item['expired'] = 1;
                        $item['expiry_reason'] = $reason;
                        return $item;
                    })->toArray();

                    // collect for bulk audit
                    $allOldMedications = array_merge($allOldMedications, $oldMedications);
                    $allNewMedications = array_merge($allNewMedications, $newMedications);
                }

                // ================= EMAIL =================
                $emailTemplate = EmailTemplate::where('template_name', 'Forced Re-registration Notice')->first();

                if ($emailTemplate && $user) {

                    if (
                        $emailTemplate->is_mandatory === 1 ||
                        ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                    ) {

                        $userdata = [
                            'firstname' => $user->full_name,
                            'drug_name' => $drug->drug_name,
                            'reason' => $reason,
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
            }

            // ================= BULK AUDIT =================
            if (!empty($allOldMedications)) {

                $moduleName = $type . ' Registration';

                CustomFunctions::audit(
                    module: $moduleName,
                    action: 'UPDATE',
                    referenceId: $request->user_id,
                    referenceTable: $table,
                    oldValues: $allOldMedications,
                    newValues: $allNewMedications,
                    description: 'Admin forced re-registration for multiple drugs: ' . implode(', ', $drugNames)
                );
            }

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Selected drugs expired and emails sent successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to get Registered Drug Status
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 23 Mar 2026
     *
     * @updated-on: N/A
     */

    public function getRegisteredDrugStatus(Request $request)
    {
        $today = Carbon::today();

        try {
            $request->validate([
                'drug_id' => 'required|integer',
                'user_id' => 'required|integer',
                'inst_id' => 'required|integer',
            ]);

            $pharmacist = PharmacistDetails::where('user_id', $request->user_id)->where('institution_id',$request->inst_id)->first();

            Log::info('pharmacist');
            Log::info($pharmacist);

            $hasLeadPharmacist = 0;

            if ($pharmacist && $pharmacist->institution_id) {
                $hasLeadPharmacist = PharmacistDetails::where('institution_id', $pharmacist->institution_id)
                    ->where('role', 'Lead Pharmacist')
                    ->where('reg_status', 'Approved')
                    ->exists() ? 1 : 0;
            }

           
            // -------------------------------

            $record = PharmacistMedication::where('drug_id', $request->drug_id)
                ->where('pharmacist_id', $pharmacist->id)
                ->where('expired', 0)
                ->where('version', function ($sub) use ($request) {
                    $sub->selectRaw('COALESCE(MAX(version), 1)')
                        ->from('pharmacist_medication')
                        ->where('drug_id', $request->drug_id)
                        ->where('user_id', $request->user_id);
                })
                ->orderByDesc('id')
                ->first();

            if (!$record) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'No Registered Drug found',
                    'is_registered' => 0,
                    'is_expired' => 0,

                    // ✅ NEW FIELD
                    'has_lead_pharmacist' => $hasLeadPharmacist,
                ]);
            }

            $isExpired = 0;

            if ($record->end_date) {
                $isExpired = Carbon::parse($record->end_date)->lt($today) ? 1 : 0;
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Drug status fetched successfully',
                'is_registered' => 1,
                'is_expired' => $isExpired,

                // ✅ NEW FIELD
                'has_lead_pharmacist' => $hasLeadPharmacist,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to fetch active drugs by hospital id.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 Mar 2026
     *
     * @updated-on: N/A
     */
    public function getInstitutionDrugs(Request $request)
    {
        try {

            $role = $request->role;
            $userId = $request->user_id;

            // =====================================================
            // 🔹 PHARMACIST
            // =====================================================
            if ($role === 'Pharmacist') {
                // 🔹 Get all pharmacists under institution
                $pharmacist_ids = PharmacistDetails::where('institution_id', $request->institution_id)
                    ->pluck('id');
                // 🔹 Get pharmacist drugs (latest per drug)
                $records = PharmacistMedication::where('user_id', $userId)
                    ->whereIn('pharmacist_id', $pharmacist_ids)
                    ->orderByDesc('version')
                    ->get()
                    ->groupBy('drug_id')
                    ->map(fn($items) => $items->first()) // latest version
                    ->values();
            }

            // =====================================================
            // 🔹 PRESCRIBER
            // =====================================================
            if ($role === 'Prescriber') {

                $prescriber_ids = PrescriberDetails::where('institution_id', $request->institution_id)
                    ->pluck('id');

                $records = PrescriberMedication::where('user_id', $userId)
                    ->whereIn('prescriber_id', $prescriber_ids)
                    ->orderByDesc('version')
                    ->get()
                    ->groupBy('drug_id')
                    ->map(fn($items) => $items->first()) // latest version
                    ->values();
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Data retrieved successfully',
                'data' => $records ?? [],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Error processing request',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to fetch Unregistered active drugs details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 18 Feb 2026
     *
     * @updated-on: N/A
     */
    public function getUnregisteredDrugs(Request $request)
    {
        try {

            $role = $request->role;
            $userId = $request->user_id;
            $institutionId = $request->institution_id;

            $records = collect();
            $drugIds = [];

            // =====================================================
            // 🔹 PHARMACIST
            // =====================================================
            if ($role === 'Pharmacist') {

                $pharmacist_ids = PharmacistDetails::where('institution_id', $institutionId)
                    ->pluck('id');

                $records = PharmacistMedication::where('user_id', $userId)
                    ->whereIn('pharmacist_id', $pharmacist_ids)
                    ->orderByDesc('version')
                    ->get();
                $drugIds = $records->pluck('drug_id')->toArray();
            }

            // =====================================================
            // 🔹 PRESCRIBER
            // =====================================================
            if ($role === 'Prescriber') {

                $prescriber_ids = PrescriberDetails::where('institution_id', $institutionId)
                    ->pluck('id');

                $records = PrescriberMedication::where('user_id', $userId)
                    ->whereIn('prescriber_id', $prescriber_ids)
                    ->orderByDesc('version')
                    ->get();

                $drugIds = $records->pluck('drug_id')->toArray();

            }

            // =====================================================
            // 🔹 GET REMAINING DRUGS (NOT ASSIGNED)
            // =====================================================
            $remainingDrugs = Drugs::with('drugStrength', 'cycles', 'capsules')
                ->where('status', 1)
                ->when(!empty($drugIds), function ($q) use ($drugIds) {
                    $q->whereNotIn('id', $drugIds);
                })
                ->get();

            // =====================================================
            // 🔹 MERGE BOTH
            // =====================================================
            $finalData = collect();

            // If assigned drugs exist → attach drug details
            if ($records->isNotEmpty()) {
                $assignedDrugDetails = Drugs::with('drugStrength', 'cycles', 'capsules')
                    ->whereIn('id', $drugIds)
                    ->get();

                $finalData = $assignedDrugDetails->merge($remainingDrugs);
            } else {
                $finalData = $remainingDrugs;
            }
            Log::info('$finalData');
            Log::info($finalData);

            return response()->json([
                'status' => 'S',
                'message' => 'Data retrieved successfully',
                'drug' => $finalData->values(),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Error processing request',
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to fetch all drugs.
     *
     * @author: Raghavendra s
     *
     * @created-on: 16 Apr 2026
     *
     * @updated-on: N/A
     */
    public function fetchAllDrugs()
    {
        try {
            $drugs = Drugs::orderBy("sequence", "asc")->get(["id", "drug_name", "id as drug_id"]);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'drugs' => $drugs]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to expiring selected drug.
     *
     * @author: Santhosha G
     *
     * @created-on: 21 Apr 2026
     *
     * @updated-on: N/A
     */
    public function forceToReRegisterDrugLevel(Request $request)
    {
        DB::beginTransaction();

        try {
            // ================= VALIDATION =================
            $validator = Validator::make($request->all(), [
                'drug_id' => 'required|exists:drugs,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'E',
                    'message' => $validator->errors()->first(),
                ]);
            }

            $drug = Drugs::find($request->drug_id);

            if (!$drug) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Drug not found',
                ]);
            }

            $reason = 'Admin Forced Re-registration';

            // ================= EMAIL TEMPLATE =================
            $emailTemplate = EmailTemplate::where('template_name', 'Forced Re-registration Notice')->first();

            $totalProcessed = 0;

            // ================= COMMON FUNCTION =================
            $expireAndNotify = function ($records, $tableName, $moduleName) use ($reason, $drug, $emailTemplate, &$totalProcessed) {

                foreach ($records as $record) {

                    $oldData = [
                        'expired' => $record->expired,
                        'expiry_reason' => $record->expiry_reason,
                    ];

                    // ================= UPDATE =================
                    $record->update([
                        'expired' => 1,
                        'expiry_reason' => $reason,
                        'updated_at' => now(),
                        'updated_by' => auth()->id(),
                    ]);

                    $user = $record->user;

                    // ================= EMAIL =================
                    if ($user && $emailTemplate) {

                        if (
                            $emailTemplate->is_mandatory == 1 ||
                            ($emailTemplate->is_mandatory == 0 && $user->email_subscription == 1)
                        ) {

                            $userdata = [
                                'firstname' => $user->full_name,
                                'drug_name' => $drug->drug_name,
                                'reason' => $reason,
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

                    // ================= AUDIT =================
                    CustomFunctions::audit(
                        module: $moduleName,
                        action: 'FORCE RE-REGISTER',
                        referenceId: $record->id,
                        referenceTable: $tableName,
                        oldValues: $oldData,
                        newValues: [
                            'expired' => 1,
                            'expiry_reason' => $reason,
                        ],
                        changedFields: ['expired', 'expiry_reason'],
                        description: 'Admin forced re-registration for drug: ' . $drug->drug_name
                    );

                    $totalProcessed++;
                }
            };

            // ================= PHARMACIST =================
            $pharmacistRecords = PharmacistMedication::with('user')
                ->where('drug_id', $request->drug_id)
                ->where('expired', 0)
                ->get();

            $expireAndNotify(
                $pharmacistRecords,
                'pharmacist_medications',
                'Pharmacist Registration'
            );

            // ================= PRESCRIBER =================
            $prescriberRecords = PrescriberMedication::with('user')
                ->where('drug_id', $request->drug_id)
                ->where('expired', 0)
                ->get();

            $expireAndNotify(
                $prescriberRecords,
                'prescriber_medications',
                'Prescriber Registration'
            );

            DB::commit();

            if ($totalProcessed === 0) {

                DB::rollBack();

                return response()->json([
                    'status' => 'E',
                    'message' => 'No active registrations found for this drug. No users are registered or all records are already expired.',
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => "$totalProcessed records expired and emails sent successfully",
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Force Re-register Error: ' . $e->getMessage());

            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to checkLeadPharmacist.
     *
     * @author: Raghavendra kumar S
     *
     * @created-on: 29 Apr 2026
     *
     * @updated-on: N/A
     */

    public function checkLeadPharmacist($institution_id)
    {
        try {
            $hasLeadPharmacist = PharmacistDetails::where('institution_id', $institution_id)
                ->where('role', 'Lead Pharmacist')
                ->where('reg_status', 'Approved')
                ->exists() ? 1 : 0;

            return response()->json([
                'status' => 'S',
                'has_lead_pharmacist' => $hasLeadPharmacist,
                'message' => $hasLeadPharmacist
                ? 'Lead Pharmacist exists'
                : 'No Lead Pharmacist found',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
