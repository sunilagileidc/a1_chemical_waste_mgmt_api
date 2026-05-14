<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
use App\Models\DrugCapsules;
use App\Models\DrugCycles;
use App\Models\DrugIndications;
use App\Models\EmailTemplate;
use App\Models\Institutions;
use App\Models\NonConformanceRules;
use App\Models\PAFConfirmation;
use App\Models\PAFConfirmationText;
use App\Models\PafDetails;
use App\Models\PafDrugCycle;
use App\Models\PafHeader;
use App\Models\PAFNonConformance;
use App\Models\PAFOfflabelConfirmation;
use App\Models\PafRequestInformation;
use App\Models\PrescriberDetails;
use App\Models\PrescriberMedication;
use App\Models\Strengths;
use App\Models\SystemParameters;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Log;
use Mail;

class PAFApiController extends Controller
{
    public function fetchPrescriberData(Request $request)
    {
        try {

            // Fetch user with role first
            $prescriber = User::with('prescriber_data')
                ->where('id', $request->user_id)
                ->first(['id', 'name', 'lastname', 'email']);

            if (!$prescriber) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'prescriber' => $prescriber,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function getPrescriberDrugs(Request $request)
    {
        try {

            $prescriber_ids = PrescriberDetails::where('institution_id', $request->institution_id)
                ->pluck('id');

            $prescriber_drugs = PrescriberMedication::with('drug')
                ->where('user_id', $request->user_id)
                ->whereIn('prescriber_id', $prescriber_ids)
                ->where('expired', 0)
                ->get();

            if (!$prescriber_drugs) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'prescriber_drugs' => $prescriber_drugs,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function fetchDrugDetails(Request $request)
    {
        try {

            $drug_id = $request->drug_id;

            $drug_capsules = DrugCapsules::where('drug_id', $drug_id)->get(['id', 'no_of_capsules']);

            $drug_indications = DrugIndications::where('drug_id', $drug_id)->get(['id', 'indication_id']);

            $drug_cycles = DrugCycles::where('drug_id', $drug_id)->get(['id', 'no_of_cycle_weeks']);

            $drug_strength = Strengths::where('status', 1)->where('drug_id', $drug_id)->get(['id', 'capsule_strength']);

            if ($drug_capsules->isEmpty() && $drug_indications->isEmpty() && $drug_cycles->isEmpty() && $drug_strength->isEmpty()) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Drug details not found',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'drug_capsules' => $drug_capsules,
                'drug_indications' => $drug_indications,
                'drug_cycles' => $drug_cycles,
                'drug_strength' => $drug_strength,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }

    }

    public function validatePafConformance(Request $request)
    {
        $data = $request->all();
        $nonConformances = [];

        // -----------------------------------------
        // Extract numbers
        // -----------------------------------------

        preg_match('/\d+/', $data['cycles'], $cycleMatches);
        $cycles = isset($cycleMatches[0]) ? (int) $cycleMatches[0] : 0;

        preg_match('/\d+/', $data['supply_weeks'], $weekMatches);
        $supplyWeeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

        preg_match('/\d+/', $data['total_supply'], $totalSupplyMatches);
        $totalSupplyWeeks = isset($totalSupplyMatches[0]) ? (int) $totalSupplyMatches[0] : 0;

        $drugName = CustomFunctions::getDrugName($data['drug_id']);

        // -----------------------------------------
        // WCBP Rules
        // -----------------------------------------

        if ($data['patient_category'] === 'WCBP') {

            // R165
            if (!isset($data['indication']) || $data['indication'] === null) {
                $msg = CustomFunctions::getNonConformanceMessage('INDICATION_REQUIRED');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }

            // R166
            if ($cycles > 1) {
                $msg = CustomFunctions::getNonConformanceMessage('WCBP_MAX_1_CYCLE');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }

            // R167
            if ($totalSupplyWeeks > 4) {
                $msg = CustomFunctions::getNonConformanceMessage('WCBP_MAX_4_WEEKS_SUPPLY');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }

            // Negative pregnancy test validation
            $negPregDate = $data['neg_preg_date'] ?? null;

            if (!$negPregDate) {
                $msg = CustomFunctions::getNonConformanceMessage('WCBP_NEG_PREG_INVALID_RANGE');
                if ($msg) {
                    $nonConformances[] = $msg;
                }

            } else {

                $negDate = Carbon::parse($negPregDate)->startOfDay();
                $referenceDate = !empty($data['date'])
                ? Carbon::parse($data['date'])->startOfDay()
                : Carbon::today();
                $threeDaysAgo = $referenceDate->copy()->subDays(3);

                if ($negDate->lt($threeDaysAgo) || $negDate->gt($referenceDate)) {

                    $msg = CustomFunctions::getNonConformanceMessage('WCBP_NEG_PREG_INVALID_RANGE');
                    if ($msg) {
                        $nonConformances[] = $msg;
                    }
                }
            }

            // -----------------------------------------
            // Thalidomide rule
            // -----------------------------------------

            if (in_array($drugName, ['50mg - Thalidomide', '100mg - Thalidomide Tablet'])) {

                foreach ($data['dosage'] as $dose) {

                    preg_match('/\d+/', $dose['capsules'], $capsuleMatches);
                    $capsules = isset($capsuleMatches[0]) ? (int) $capsuleMatches[0] : 0;

                    $expectedCapsules = $supplyWeeks * 7;

                    if ($capsules != $expectedCapsules) {

                        $msg = CustomFunctions::getNonConformanceMessage('THALIDOMIDE_DOSAGE_RULE');
                        if ($msg) {
                            $nonConformances[] = $msg;
                        }
                    }
                }
            }

            // -----------------------------------------
            // Other indication rule
            // -----------------------------------------

            if (($data['is_other_indc'] ?? 0) == 1) {

                $msg = CustomFunctions::getNonConformanceMessage('OTHER_INDICATION_REVIEW');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }
        }

        // -----------------------------------------
        // WNCBP Rules
        // -----------------------------------------

        if ($data['patient_category'] === 'WNCBP') {

            // R176
            if (!isset($data['indication']) || $data['indication'] === null) {
                $msg = CustomFunctions::getNonConformanceMessage('INDICATION_REQUIRED');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }

            // -----------------------------------------
            // Thalidomide rule
            // -----------------------------------------

            if (in_array($drugName, ['50mg - Thalidomide', '100mg - Thalidomide Tablet'])) {

                foreach ($data['dosage'] as $dose) {

                    preg_match('/\d+/', $dose['capsules'], $capsuleMatches);
                    $capsules = isset($capsuleMatches[0]) ? (int) $capsuleMatches[0] : 0;

                    $expectedCapsules = $supplyWeeks * 7;

                    if ($capsules != $expectedCapsules) {

                        $msg = CustomFunctions::getNonConformanceMessage('THALIDOMIDE_DOSAGE_RULE');
                        if ($msg) {
                            $nonConformances[] = $msg;
                        }
                    }
                }
            }

            // R178
            if ($totalSupplyWeeks > 12) {

                $msg = CustomFunctions::getNonConformanceMessage('WNCBP_MAX_12_WEEKS_SUPPLY');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }

            // Other indication rule

            if (($data['is_other_indc'] ?? 0) == 1) {

                $msg = CustomFunctions::getNonConformanceMessage('OTHER_INDICATION_REVIEW');
                if ($msg) {
                    $nonConformances[] = $msg;
                }
            }
        }

        // -----------------------------------------
        // Under 18 Off-label rule (All categories)
        // -----------------------------------------

        $age = Carbon::parse($data['dob'])->age;

        if ($age < 18) {
            $msg = CustomFunctions::getNonConformanceMessage('UNDER_18_OFF_LABEL');
            if ($msg) {
                $nonConformances[] = $msg;
            }
        }

        // -----------------------------------------
        // Risk confirmation rule (All categories)
        // -----------------------------------------

        if ($data['risk_confirmed'] == 0) {
            $msg = CustomFunctions::getNonConformanceMessage('RISK_NOT_CONFIRMED');
            if ($msg) {
                $nonConformances[] = $msg;
            }
        }

        return response()->json([
            'status' => 'S',
            'non_conformances' => $nonConformances,
            'has_nc' => count($nonConformances) > 0,
        ]);
    }

    public function createPaf(Request $request)
    {
        $data = $request->all();
        $userId = auth()->id();

        DB::beginTransaction();

        try {

            // ---------------------------------------------------
            // 1 Check if patient already exists
            // ---------------------------------------------------

            if (!empty($data['patient_id'])) {

                // Use existing header
                $pafHeader = PafHeader::where('patient_no', $data['patient_id'])->first();

                if (!$pafHeader) {
                    return response()->json([
                        'status' => 'E',
                        'message' => 'Invalid patient selected',
                    ]);
                }

                $patientNo = $pafHeader->patient_no;
            } else {

                // ---------------------------------------------------
                // 2 Generate patient number (existing logic)
                // ---------------------------------------------------

                $categoryInitial = $data['patient_category'];
                $patient_initials = strtoupper($data['patient_initials']);
                $dobFormatted = date('ymd', strtotime($data['dob']));

                $basePatientKey = $categoryInitial . $patient_initials . $dobFormatted;

                $lastPatient = PafHeader::where('patient_no', 'like', $basePatientKey . '%')
                    ->orderBy('patient_no', 'desc')
                    ->first();

                if ($lastPatient) {

                    $lastSequence = (int) substr($lastPatient->patient_no, -3);
                    $nextSequence = $lastSequence + 1;

                } else {

                    $nextSequence = 1;
                }

                $sequence = str_pad($nextSequence, 3, '0', STR_PAD_LEFT);
                $patientNo = $basePatientKey . $sequence;

                // ---------------------------------------------------
                // 3 Create Header
                // ---------------------------------------------------

                $pafHeader = PafHeader::create([
                    'patient_no' => $patientNo,
                    'gender' => $data['gender'],
                    'is_active' => 1,
                    'paf_status' => 'Submitted',
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // ---------------------------------------------------
            // Check if a Submitted PAF already exists
            // (same Initials, DOB, Category, Institution)
            // ---------------------------------------------------

            $existingSubmittedPaf = PafDetails::latestVersion()
                ->where('patient_initials', strtoupper($data['patient_initials']))
                ->where('patient_dob', $data['dob'])
                ->where('patient_category', $data['patient_category'])
                ->where('institution_id', $data['institution_id'])
                ->where('status', 'Submitted')
                ->first();

            if ($existingSubmittedPaf) {

                return response()->json([
                    'status' => 'E',
                    'message' => 'There can only be one submitted prescription for a patient.',
                ]);
            }

            // ---------------------------------------------------
            // 4 Create PAF Details (no change)
            // ---------------------------------------------------

            $lastPaf = PafDetails::orderBy('paf_no', 'desc')->first();

            if ($lastPaf) {
                $nextPafNo = (int) $lastPaf->paf_no + 1;
            } else {
                $nextPafNo = 1;
            }

            $pafNo = str_pad($nextPafNo, 4, '0', STR_PAD_LEFT);

            $pafDetails = PafDetails::create([
                'paf_no' => $pafNo,
                'paf_header_id' => $pafHeader->id,
                'patient_initials' => strtoupper($data['patient_initials']),
                'patient_dob' => $data['dob'],
                'last_negative_preg_date' => $data['neg_preg_date'] ?? null,
                'prescriber_id' => $userId,
                'indication_id' => $data['indication'],
                'other_indication' => Str::title($data['other_indication']),
                'patient_category' => $data['patient_category'],
                'institution_id' => $data['institution_id'],
                'drug_id' => $data['drug_id'],
                'status' => 'Submitted',
                'version' => 1,
                'declaration_name' => $request->user()->name ?? 'N/A',
                'declaration_date' => $data['date'] ?? now(),
                'is_retrospective' => $data['is_retrospective'],
                'is_clinical_trial' => $data['is_clinical_trial'],
                'clinical_test_note' => $data['clinical_test_note'],
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // ---------------------------------------------------
            // 4.1 Handle Renewal
            // ---------------------------------------------------
            if ($data['renewal'] == 1) {

                // 1 Update parent record → mark as renewed
                PafDetails::where('id', $data['renewal_paf_parent_id'])
                    ->update(['renewal' => 1]);

                // 2 Update current record → set parent id
                $pafDetails->update([
                    'renewal_paf_parent_id' => $data['renewal_paf_parent_id'],
                ]);
            }

            // ---------------------------------------------------
            // 5 Drug cycles (no change)
            // ---------------------------------------------------

            $totalSupply = $data['total_supply'];

            foreach ($data['dosage'] as $dose) {

                $capsules = $dose['capsules'];
                $cycles = $data['cycles'];
                $supplyWeeks = $data['supply_weeks'];

                PafDrugCycle::create([
                    'paf_details_id' => $pafDetails->id,
                    'drug_strength' => $dose['strength'],
                    'cap_per_cycle' => $capsules,
                    'supply_weeks' => $supplyWeeks,
                    'no_of_cycles' => $cycles,
                    'total_supply' => $totalSupply,
                    'version' => 1,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // ---------------------------------------------------
            // 6.1 Check Drug Conformance for WCBP
            // ---------------------------------------------------

            if ($data['patient_category'] === 'WCBP') {
                $hasNonConformance = false;

                // Extract cycle number from "1 cycle", "2 cycles"
                preg_match('/\d+/', $data['cycles'], $cycleMatches);
                $cycles = isset($cycleMatches[0]) ? (int) $cycleMatches[0] : 0;

                // Extract supply weeks from "1 week", "2 weeks"
                preg_match('/\d+/', $data['supply_weeks'], $weekMatches);
                $supplyWeeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

                // Extract total supply weeks from "1 week", "2 weeks"
                preg_match('/\d+/', $data['total_supply'], $totalSupplyMatches);
                $totalSupplyWeeks = isset($totalSupplyMatches[0]) ? (int) $totalSupplyMatches[0] : 0;

                // -----------------------------------------
                // Rule → Indication required for WCBP - R165
                // -----------------------------------------
                if (!isset($data['indication']) || $data['indication'] === null) {
                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'INDICATION_REQUIRED',
                        $userId
                    );

                    $hasNonConformance = true;
                }

                // Rule 1 → cycles > 1 (R166)
                if ($cycles > 1) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'WCBP_MAX_1_CYCLE',
                        $userId
                    );

                    $hasNonConformance = true;
                }

                // Rule 2 → supply weeks > 4 (R167)
                if ($totalSupplyWeeks > 4) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'WCBP_MAX_4_WEEKS_SUPPLY',
                        $userId
                    );
                    $hasNonConformance = true;
                }

                // Rule 3 → Negative pregnancy test validation
                $negPregDate = $data['neg_preg_date'] ?? null;

                if (!$negPregDate) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'WCBP_NEG_PREG_INVALID_RANGE',
                        $userId
                    );
                    $hasNonConformance = true;

                } else {

                    $negDate = Carbon::parse($negPregDate)->startOfDay();
                    $referenceDate = !empty($data['date'])
                    ? Carbon::parse($data['date'])->startOfDay()
                    : Carbon::today();
                    $threeDaysAgo = $referenceDate->copy()->subDays(3);

                    if ($negDate->lt($threeDaysAgo) || $negDate->gt($referenceDate)) {

                        CustomFunctions::createNonConformance(
                            $pafDetails->id,
                            'WCBP_NEG_PREG_INVALID_RANGE',
                            $userId
                        );
                        $hasNonConformance = true;
                    }
                }

                // ---------------------------------------------------
                // Rule 4 → Thalidomide capsule/day rule (WCBP)
                // ---------------------------------------------------

                $drugName = CustomFunctions::getDrugName($data['drug_id']);

                if (in_array($drugName, ['50mg - Thalidomide', '100mg - Thalidomide Tablet'])) {

                    foreach ($data['dosage'] as $dose) {

                        preg_match('/\d+/', $dose['capsules'], $capsuleMatches);
                        $capsules = isset($capsuleMatches[0]) ? (int) $capsuleMatches[0] : 0;

                        preg_match('/\d+/', $data['supply_weeks'], $weekMatches);
                        $supplyWeeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

                        // 1 capsule per day → 7 capsules per week
                        $expectedCapsules = $supplyWeeks * 7;

                        if ($capsules != $expectedCapsules) {

                            CustomFunctions::createNonConformance(
                                $pafDetails->id,
                                'THALIDOMIDE_DOSAGE_RULE',
                                $userId
                            );

                            $hasNonConformance = true;
                        }
                    }
                }

                // -----------------------------------------
                // Rule → Other indication - R170
                // -----------------------------------------
                if ($data['is_other_indc'] == 1) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'OTHER_INDICATION_REVIEW',
                        $userId
                    );

                    $pafDetails->update([
                        'off_label' => 1,
                    ]);

                    $hasNonConformance = true;
                }

                // If any rule failed → mark risk HIGH
                if ($hasNonConformance) {
                    $pafDetails->update([
                        'risk_level' => 'High Risk',
                    ]);
                }

            }

            // ---------------------------------------------------
            // 6.2 Check if patient is under 18 → Off-label rule
            // Applies to ALL patient categories
            // ---------------------------------------------------

            $age = Carbon::parse($data['dob'])->age;

            if ($age < 18) {

                // mark off-label
                $pafDetails->update([
                    'off_label' => 1,
                ]);

                // create non-conformance

                CustomFunctions::createNonConformance(
                    $pafDetails->id,
                    'UNDER_18_OFF_LABEL',
                    $userId
                );

                // apply risk based on category
                if ($data['patient_category'] === 'WCBP') {

                    $pafDetails->update([
                        'risk_level' => 'High Risk',
                    ]);

                } else {

                    $pafDetails->update([
                        'risk_level' => 'Low Risk',
                    ]);
                }
            }

            // ---------------------------------------------------
            // 6.3 Check Drug Conformance for WNCBP
            // ---------------------------------------------------

            if ($data['patient_category'] === 'WNCBP') {

                $hasLowRiskNC = false;

                // Extract cycle number
                preg_match('/\d+/', $data['cycles'], $cycleMatches);
                $cycles = isset($cycleMatches[0]) ? (int) $cycleMatches[0] : 0;

                // Extract supply weeks
                preg_match('/\d+/', $data['supply_weeks'], $weekMatches);
                $supplyWeeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

                // Extract total supply weeks from "1 week", "2 weeks"
                preg_match('/\d+/', $data['total_supply'], $totalSupplyMatches);
                $totalSupplyWeeks = isset($totalSupplyMatches[0]) ? (int) $totalSupplyMatches[0] : 0;

                // -----------------------------------------
                // Rule → Indication required for WNCBP (R176)
                // -----------------------------------------
                if (!isset($data['indication']) || $data['indication'] === null) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'INDICATION_REQUIRED',
                        $userId
                    );

                    $hasLowRiskNC = true;
                }

                // ---------------------------------------------------
                // Rule 3 → Thalidomide capsule/day rule
                // ---------------------------------------------------

                $drugName = CustomFunctions::getDrugName($data['drug_id']);

                if (in_array($drugName, ['50mg - Thalidomide', '100mg - Thalidomide Tablet'])) {

                    foreach ($data['dosage'] as $dose) {

                        preg_match('/\d+/', $dose['capsules'], $capsuleMatches);
                        $capsules = isset($capsuleMatches[0]) ? (int) $capsuleMatches[0] : 0;

                        preg_match('/\d+/', $data['supply_weeks'], $weekMatches);
                        $supplyWeeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

                        // 1 capsule per day rule → 7 capsules per week
                        $expectedCapsules = $supplyWeeks * 7;

                        if ($capsules != $expectedCapsules) {

                            CustomFunctions::createNonConformance(
                                $pafDetails->id,
                                'THALIDOMIDE_DOSAGE_RULE',
                                $userId
                            );

                            $pafDetails->update([
                                'risk_level' => 'Low Risk',
                            ]);
                        }
                    }
                }
                // -----------------------------------------
                // Rule 2 → supply weeks > 12 (R178)
                // -----------------------------------------
                if ($totalSupplyWeeks > 12) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'WNCBP_MAX_12_WEEKS_SUPPLY',
                        $userId
                    );

                    $hasLowRiskNC = true;
                }

                // -----------------------------------------
                // Rule → Other indication - R181
                // -----------------------------------------
                if ($data['is_other_indc'] == 1) {

                    CustomFunctions::createNonConformance(
                        $pafDetails->id,
                        'OTHER_INDICATION_REVIEW',
                        $userId
                    );

                    $pafDetails->update([
                        'off_label' => 1,
                    ]);

                    $pafDetails->update([
                        'risk_level' => 'Low Risk',
                    ]);
                }

                // -----------------------------------------
                // Apply LOW risk if any rule triggered
                // -----------------------------------------
                if ($hasLowRiskNC) {
                    $pafDetails->update([
                        'risk_level' => 'Low Risk',
                    ]);
                }
            }

            // ---------------------------------------------------
            // 7 Create PAF Confirmation Entry
            // ---------------------------------------------------

            PAFConfirmation::create([
                'paf_detail_id' => $pafDetails->id,
                'is_confirmed' => $data['risk_confirmed'],
                'role' => 'Prescriber',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            if ($data['risk_confirmed'] == 0) {

                CustomFunctions::createNonConformance(
                    $pafDetails->id,
                    'RISK_NOT_CONFIRMED',
                    $userId
                );

                if ($data['patient_category'] === 'WCBP') {
                    $pafDetails->update([
                        'risk_level' => 'High Risk',
                    ]);
                }
            }

            // ---------------------------------------------------
            // Store Confirmation Types
            // ---------------------------------------------------

            CustomFunctions::storeOfflabelConfirmations(
                $data['confirmation_types'] ?? [],
                $pafDetails->id,
                $userId,
                CustomFunctions::getDrugName($data['drug_id']),
                $data['other_indication'] ?? ''
            );

            // ================= AUDIT: PAF CREATION =================

            CustomFunctions::audit(
                module: 'PAF',
                action: 'CREATE',
                referenceId: $pafDetails->id,
                referenceTable: 'paf_details',
                oldValues: null,
                newValues: [
                    'paf_no' => $pafDetails->paf_no,
                    'patient_no' => $patientNo,
                    'patient_initials' => $pafDetails->patient_initials,
                    'patient_category' => $pafDetails->patient_category,
                    'drug_id' => $pafDetails->drug_id,
                    'institution_id' => $pafDetails->institution_id,
                    'status' => $pafDetails->status,
                    'risk_level' => $pafDetails->risk_level ?? 'N/A',
                    'off_label' => $pafDetails->off_label ?? 0,
                ],
                changedFields: null,
                description: "PAF {$pafDetails->paf_no} created successfully for patient {$patientNo} under {$pafDetails->patient_category} category with status '{$pafDetails->status}'."
            );
            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF created successfully',
                'paf_id' => $pafHeader->id,
                'patient_no' => $patientNo,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to create PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: To get priscriber PAF details.
     *
     * @author: Santhosha G
     *
     * @created-on: 25 Mar, 2026
     *
     * @updated-on: 25 Mar, 2026
     */
    public function getPrescriberPaf(Request $request)
    {

        $institutionId = $request->institution_id;
        try {

            $userData = Auth::user();

            if ($userData->rolename == "Nurse") {
                $userId = $userData->created_user->id;
            } else {
                $userId = auth()->id();
            }

            $pafs = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion()
                        ->with([
                            'indication:id,name',
                            'drug:id,drug_name,drug_form',
                            'prescriber' => function ($q) {
                                $q->select('id', 'name', 'lastname')
                                    ->with('prescriber_data');
                            },
                            'drugCycles' => function ($q) {
                                $q->latestVersion();
                            },
                        ]);
                },
            ])
                ->whereHas('pafDetails', function ($q) use ($userId, $institutionId) {
                    $q->where('institution_id', $institutionId);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $formatted = $pafs->map(function ($paf) {

                $details = $paf->pafDetails->first();

                return [
                    'id' => $paf->id,
                    'patient_id' => $paf->patient_no,
                    'patient_initials' => $details->patient_initials ?? null,
                    'dob' => $details->patient_dob ?? null,
                    'gender' => $paf->gender ?? null,
                    'patient_category' => $details->patient_category ?? null,
                    'indication' => $details->indication->name ?? null,
                    'drug_name' => $details->drug->drug_name ?? null,
                    'is_retrospective' => $details->is_retrospective ?? null,
                    'is_clinical_trial' => $details->is_clinical_trial ?? null,
                    'clinical_test_note' => $details->clinical_test_note ?? null,
                    'prescriber' => $details && $details->prescriber
                    ? $details->prescriber->name . ' ' . $details->prescriber->lastname
                    : null,
                    'paf_status' => $paf->paf_status,
                    'slug' => $paf->slug,
                    'created_at' => $paf->created_at,
                    'non_conformance' => $details->non_conformance,

                    // COUNT
                    'paf_details_count' => $paf->pafDetails->count(),

                    // ARRAY OF PAF DETAILS
                    'paf_details_list' => $paf->pafDetails->map(function ($detail) {
                        return [
                            'paf_no' => $detail->paf_no ?? null,
                            'declaration_date' => $detail->declaration_date ?? null,
                            'declaration_name' => $detail->declaration_name ?? null,
                            'status' => $detail->status ?? null,
                        ];
                    })->values(),
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'PAFs fetched successfully',
                'data' => $formatted,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch PAFs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPafStats(Request $request)
    {
        $institutionId = $request->institution_id;
        try {

            $userData = Auth::user();

            if ($userData->rolename == "Nurse") {
                $userId = $userData->created_user->id;
            } else {
                $userId = auth()->id();
            }

            $pafs = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion()
                        ->with([
                            'indication:id,name',
                            'institutions:id,name,institution_type,address,pharmacy_id',
                            'drug:id,drug_name,drug_form',
                            'mah_data:id,contact_name,contact_email,logo',
                            'dispensing_loc:id,name,ref_number',
                            'prescriber' => function ($q) {
                                $q->select('id', 'name', 'lastname')
                                    ->with('prescriber_data');
                            },
                            'drugCycles' => function ($q) {
                                $q->latestVersion();
                            },
                        ]);
                },
            ])
                ->whereHas('pafDetails', function ($q) use ($userId, $institutionId) {
                    $q->where('institution_id', $institutionId);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            // Extract ONLY related paf_details
            $allDetails = $pafs->flatMap(function ($paf) {
                return $paf->pafDetails;
            });

            // Counts based on filtered data
            $totalPafs = $allDetails->count();

            $totalPatients = $pafs->count();

            $overduePaf = $allDetails->where('status', 'Action Required')->count();

            $rejectedPaf = $allDetails->where('status', 'Rejected')->count();

            $activePatients = $allDetails->whereIn('status', ['Action Required', 'Submitted'])->count();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF stats fetched successfully',
                'stats' => [
                    [
                        'label' => 'Total PAFs',
                        'value' => $totalPafs,
                    ],
                    [
                        'label' => 'Total patients',
                        'value' => $totalPatients,
                    ],
                    [
                        'label' => 'Active Patients',
                        'value' => $activePatients,
                    ],
                    [
                        'label' => 'Overdue PAF (Action Required)',
                        'value' => $overduePaf,
                    ],
                    [
                        'label' => 'Rejected PAF',
                        'value' => $rejectedPaf,
                    ],
                ],
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getPafDetails(Request $request)
    {
        try {

            $slug = $request->slug;
            $institutionId = $request->institution_id;

            $institution = Institutions::select('id', 'institution_type')
                ->findOrFail($institutionId);

            $pafQuery = PafHeader::with([
                'pafDetails' => function ($query) use ($institution, $institutionId) {

                    $query->latestVersion();

                    if (
                        $institution->institution_type === 'Outpatient Pharmacy' ||
                        $institution->institution_type === 'Homecare'
                    ) {
                        $query->where('dispensing_loc_id', $institutionId)
                            ->where('status', 'Approved');
                    }

                    $query->with([
                        'indication:id,name',
                        'institutions:id,name,institution_type,address,pharmacy_id',
                        'drug:id,drug_name,drug_form',
                        'mah_data:id,contact_name,contact_email,logo',
                        'dispensing_loc:id,name,ref_number',
                        'prescriber' => function ($q) {
                            $q->select('id', 'name', 'lastname')
                                ->with('prescriber_data');
                        },
                        'drugCycles' => function ($q) {
                            $q->latestVersion();
                        },
                        'prescribedDrugCycles',
                    ]);
                },
            ])
                ->where('slug', $slug);

            if (
                $institution->institution_type === 'Outpatient Pharmacy' ||
                $institution->institution_type === 'Homecare'
            ) {
                $pafQuery->whereHas('pafDetails', function ($q) use ($institutionId) {
                    $q->latestVersion()
                        ->where('dispensing_loc_id', $institutionId)
                        ->where('status', 'Approved');
                });
            }

            $paf = $pafQuery->first();

            $paf->pafDetails->each(function ($detail) {

                $rootId = $detail->parent_id ?? $detail->id;

                $detail->prescribed_drug_cycles = PafDrugCycle::where('paf_details_id', $rootId)
                    ->where('version', 1)
                    ->get();

                // Offlabel confirmations from parent/root PAF
                $detail->offlabel_confirmations = PAFOfflabelConfirmation::where('paf_details_id', $rootId)
                    ->get(['id', 'type', 'confirmation']);
            });

            if (!$paf) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Data fetched successfully.',
                'paf' => $paf,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'F',
                'message' => 'Failed to fetch PAF details',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function fetchPatientInitials(Request $request)
    {
        try {

            $patientCategory = $request->patient_category;
            $initials = $request->initials;
            $hospital_id = $request->hospital_id;
            $patient_dob = $request->patient_dob ? Carbon::parse($request->patient_dob)->format('Y-m-d') : null;

            $patients = PafDetails::select(
                'paf_header_id',
                'patient_initials',
                'patient_dob',
                'patient_category',
                'institution_id'
            )
                ->with('header:id,patient_no')
                ->where('patient_category', $patientCategory)
                ->when($patient_dob, function ($q) use ($patient_dob) {
                    $q->whereDate('patient_dob', $patient_dob);
                })
                ->where('institution_id', $hospital_id)
                ->when($initials, function ($q) use ($initials) {
                    $q->where('patient_initials', 'LIKE', $initials . '%');
                })
                ->groupBy(
                    'patient_initials',
                    'patient_dob',
                    'patient_category',
                    'institution_id',
                    'paf_header_id'
                )
                ->get();

            $formatted = $patients->map(function ($patient) {

                return [
                    'patient_initials' => $patient->patient_initials,
                    'patient_no' => $patient->header->patient_no ?? null,
                    'patient_dob' => $patient->patient_dob,
                    'patient_category' => $patient->patient_category,
                    'institution_id' => $patient->institution_id,
                    'disp_initial' => $patient->patient_initials . ' - ' . ($patient->header->patient_no ?? ''),
                ];

            });

            return response()->json([
                'status' => 'S',
                'data' => $formatted,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'F',
                'message' => 'Failed to fetch patient initials',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to fetch pharmacist PAF details.
     *
     * @author: Santhosha G
     *
     * @created-on: 17 Mar 2026
     *
     * @updated-on: 17 Mar 2026
     */
    public function getPharmacistPaf(Request $request)
    {
        $institutionId = $request->institution_id;

        try {

            $institution = Institutions::select('id', 'institution_type')
                ->findOrFail($institutionId);

            $userId = auth()->id();

            $pafQuery = PafHeader::query();

            if ($institution->institution_type === 'Inpatient Pharmacy') {

                $hospitalIds = Institutions::where('pharmacy_id', $institutionId)
                    ->pluck('id');

                $pafQuery->whereHas('pafDetails', function ($q) use ($hospitalIds) {
                    $q->latestVersion()
                        ->whereIn('institution_id', $hospitalIds);
                });

                $pafQuery->with([
                    'pafDetails' => function ($q) use ($hospitalIds) {
                        $q->latestVersion()
                            ->whereIn('institution_id', $hospitalIds)
                            ->with([
                                'indication:id,name',
                                'drug:id,drug_name,drug_form',
                                'prescriber:id,name,lastname',
                            ]);
                    },
                ]);

            } elseif (
                $institution->institution_type === 'Outpatient Pharmacy' ||
                $institution->institution_type === 'Homecare'
            ) {

                $pafQuery->whereHas('pafDetails', function ($q) use ($institutionId) {
                    $q->latestVersion()
                        ->where('dispensing_loc_id', $institutionId)
                        ->where('status', 'Approved');
                });

                $pafQuery->with([
                    'pafDetails' => function ($q) use ($institutionId) {
                        $q->latestVersion()
                            ->where('dispensing_loc_id', $institutionId)
                            ->where('status', 'Approved')
                            ->with([
                                'indication:id,name',
                                'drug:id,drug_name,drug_form',
                                'prescriber:id,name,lastname',
                            ]);
                    },
                ]);
            }

            $pafs = $pafQuery
                ->orderByDesc('created_at')
                ->get();

            $formatted = $pafs->map(function ($paf) {

                $details = $paf->pafDetails->first();

                return [
                    'id' => $paf->id,
                    'patient_id' => $paf->patient_no,
                    'patient_initials' => $details->patient_initials ?? null,
                    'dob' => $details->patient_dob ?? null,
                    'gender' => $paf->gender ?? null,
                    'patient_category' => $details->patient_category ?? null,
                    'indication' => $details->indication->name ?? null,
                    'drug_name' => $details->drug->drug_name ?? null,
                    'is_retrospective' => $details->is_retrospective ?? null,
                    'is_clinical_trial' => $details->is_clinical_trial ?? null,
                    'clinical_test_note' => $details->clinical_test_note ?? null,
                    'prescriber' => $details && $details->prescriber
                    ? $details->prescriber->name . ' ' . $details->prescriber->lastname
                    : null,
                    'paf_status' => $paf->paf_status,
                    'non_conformance' => $details->non_conformance,
                    'slug' => $paf->slug,
                    'created_at' => $paf->created_at,
                    'paf_details_count' => $paf->pafDetails->count(),
                    'paf_details_list' => $paf->pafDetails->map(function ($detail) {
                        return [
                            'paf_no' => $detail->paf_no ?? null,
                            'declaration_date' => $detail->declaration_date ?? null,
                            'declaration_name' => $detail->declaration_name ?? null,
                            'status' => $detail->status ?? null,
                        ];
                    })->values(),
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'PAFs fetched successfully',
                'data' => $formatted,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch PAFs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function rejectPaf(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $request->validate([
                'id' => 'required|exists:paf_details,id',
                'reason' => 'required|string|max:255',
            ]);

            // ---------------------------------------------------
            // 1 Get current PAF
            // ---------------------------------------------------
            $current = PafDetails::find($request->id);

            if (!$current) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            // Optional safety
            if ($current->status === 'Rejected') {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF already rejected',
                ]);
            }

            // ---------------------------------------------------
            // 2 Find parent ID (root)
            // ---------------------------------------------------
            $parentId = $current->parent_id ?: $current->id;

            // ---------------------------------------------------
            // 3 Get latest version
            // ---------------------------------------------------
            $latest = PafDetails::where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
            })
                ->orderByDesc('version')
                ->first();

            $newVersion = $latest->version + 1;

            // ---------------------------------------------------
            // 4 Create NEW VERSION (Rejected)
            // ---------------------------------------------------
            $newPaf = PafDetails::create([
                'paf_no' => $latest->paf_no,
                'paf_header_id' => $latest->paf_header_id,
                'patient_initials' => $latest->patient_initials,
                'patient_dob' => $latest->patient_dob,
                'last_negative_preg_date' => $latest->last_negative_preg_date,
                'prescriber_id' => $latest->prescriber_id,
                'indication_id' => $latest->indication_id,
                'other_indication' => Str::title($latest->other_indication),
                'patient_category' => $latest->patient_category,
                'institution_id' => $latest->institution_id,
                'drug_id' => $latest->drug_id,

                // rejection fields
                'rejection_reason' => $request->reason,
                'status' => 'Rejected',

                // versioning
                'version' => $newVersion,
                'parent_id' => $parentId,

                'admin_notes' => $latest->admin_notes,
                'is_reviewed' => $latest->is_reviewed,
                'off_label' => $latest->off_label,
                'risk_level' => $latest->risk_level,
                'renewal' => $latest->renewal,
                'renewal_paf_parent_id' => $latest->renewal_paf_parent_id,

                // carry forward
                'declaration_date' => $latest->declaration_date,
                'declaration_name' => $latest->declaration_name,
                'is_retrospective' => $latest->is_retrospective,
                'is_clinical_trial' => $latest->is_clinical_trial,
                'clinical_test_note' => $latest['clinical_test_note'],
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Copy non-conformances from old to new
            CustomFunctions::copyNonConformances(
                $latest->id,
                $newPaf->id,
                $userId
            );

            // ---------------------------------------------------
            // 4.1 Other Rejection Rule
            // ---------------------------------------------------

            if ($request->is_other_rejection == 1) {

                CustomFunctions::createNonConformance(
                    $newPaf->id,
                    'OTHER_REJECTION_REVIEW',
                    $userId
                );

                // Update risk based on patient category
                if ($newPaf->patient_category === 'WCBP') {

                    $newPaf->update([
                        'risk_level' => 'High Risk',
                    ]);

                } elseif ($newPaf->patient_category === 'WNCBP') {

                    $newPaf->update([
                        'risk_level' => 'Low Risk',
                    ]);
                }
            }

            // ---------------------------------------------------
            // 5 Clone latest DRUG CYCLE (no change)
            // ---------------------------------------------------
            $latestVersion = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })->max('version');

            $latestCycles = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })
                ->where('version', $latestVersion)
                ->get();

            if ($latestCycles->isEmpty()) {
                throw new \Exception("No drug cycles found");
            }

            foreach ($latestCycles as $cycle) {

                $cycleParentId = $cycle->parent_id ?: $cycle->id;

                PafDrugCycle::create([
                    'paf_details_id' => $newPaf->id,

                    'drug_strength' => $cycle->drug_strength,
                    'cap_per_cycle' => $cycle->cap_per_cycle,
                    'no_of_cycles' => $cycle->no_of_cycles,
                    'total_supply' => $cycle->total_supply,
                    'supply_weeks' => $cycle->supply_weeks,

                    'version' => $cycle->version + 1,
                    'parent_id' => $cycleParentId,

                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // ================= AUDIT: PAF REJECTION =================

            CustomFunctions::audit(
                module: 'PAF',
                action: 'REJECT',
                referenceId: $newPaf->id,
                referenceTable: 'paf_details',
                oldValues: [
                    'paf_id' => $latest->id,
                    'status' => $latest->status,
                    'version' => $latest->version,
                ],
                newValues: [
                    'paf_id' => $newPaf->id,
                    'status' => $newPaf->status,
                    'version' => $newPaf->version,
                    'rejection_reason' => $request->reason,
                    'risk_level' => $newPaf->risk_level ?? 'N/A',
                ],
                changedFields: ['status', 'version', 'rejection_reason'],
                description: "PAF {$newPaf->paf_no} (Version {$latest->version}) has been rejected and a new version {$newPaf->version} is created. Reason: {$request->reason}. Patient Category: {$newPaf->patient_category}, Risk Level: " . ($newPaf->risk_level ?? 'N/A')
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF rejected successfully',
                'data' => $newPaf,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to reject PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pafApproveAndDispense(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $request->validate([
                'paf_details_id' => 'required|exists:paf_details,id',
                'cycles' => 'required',
                'total_supply' => 'required',

                'drug_cycles' => 'required|array|min:1',
                'drug_cycles.*.drug_strength' => 'required',
                'drug_cycles.*.cap_per_cycle' => 'required',
            ]);

            // ================= 1 GET CURRENT =================
            $current = PafDetails::find($request->paf_details_id);

            if (!$current) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            // ================= 2 PARENT =================
            $parentId = $current->parent_id ?: $current->id;

            // ================= 3 LATEST =================
            $latest = PafDetails::where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
            })->orderByDesc('version')->first();

            $newVersion = $latest->version + 1;

            // ================= 4 CREATE NEW PAF =================
            $is_inpatient = $request->is_inpatient ?? 0;
            $newPaf = PafDetails::create([
                'paf_no' => $latest->paf_no,
                'paf_header_id' => $latest->paf_header_id,
                'patient_initials' => $latest->patient_initials,
                'patient_dob' => $latest->patient_dob,
                'prescriber_id' => $latest->prescriber_id,
                'indication_id' => $latest->indication_id,
                'patient_category' => $latest->patient_category,
                'institution_id' => $latest->institution_id,
                'drug_id' => $latest->drug_id,
                'rejection_reason' => $latest->rejection_reason,
                'declaration_date' => $latest->declaration_date,
                'declaration_name' => $latest->declaration_name,

                'last_negative_preg_date' => $request->last_negative_preg_date,
                'status' => 'Dispensed',
                'version' => $newVersion,
                'parent_id' => $parentId,
                'mah_id' => $request->mah_id ?? null,
                'is_inpatient' => $is_inpatient,
                'dispensing_sig' => $request->dispensing_name,
                'dispensing_date' => $request->dispensing_date,
                'admin_notes' => $latest->admin_notes,
                'is_reviewed' => $latest->is_reviewed,
                'off_label' => $latest->off_label,
                'risk_level' => $latest->risk_level,
                'other_indication' => $latest->other_indication,
                'renewal' => $latest->renewal,
                'renewal_paf_parent_id' => $latest->renewal_paf_parent_id,
                'is_retrospective' => $latest->is_retrospective,
                'is_clinical_trial' => $latest->is_clinical_trial,
                'clinical_test_note' => $latest->clinical_test_note,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            CustomFunctions::applyNonConformanceRules(
                $latest,
                $newPaf,
                $request,
                $userId
            );

            PAFConfirmation::create([
                'paf_detail_id' => $parentId,
                'is_confirmed' => $request->risk_confirmed,
                'role' => 'Pharmacist',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // ================= 5 DRUG CYCLE =================
            $latestVersion = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })->max('version');

            $latestCycles = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })
                ->where('version', $latestVersion)
                ->get();

            // ================= 6 LOOP MULTIPLE ROWS =================
            foreach ($request->drug_cycles as $index => $cycleData) {

                $baseCycle = $latestCycles[$index] ?? $latestCycles->first();

                $cycleParentId = $baseCycle->parent_id ?: $baseCycle->id;

                PafDrugCycle::create([
                    'paf_details_id' => $newPaf->id,

                    'drug_strength' => $cycleData['drug_strength'],
                    'cap_per_cycle' => $cycleData['cap_per_cycle'],

                    'no_of_cycles' => $request->cycles,
                    'total_supply' => $request->total_supply,

                    'supply_weeks' => $baseCycle->supply_weeks,

                    'version' => $latestVersion + 1,
                    'parent_id' => $cycleParentId,

                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // AFTER COMMIT → MAIL + AUDIT
            try {

                // Only for OFF LABEL
                if ($newPaf->off_label == 1) {

                    $paf = PafDetails::with([
                        'drug',
                        'indication',
                        'institutions.pharmacy',
                        'header',
                        'mah_data',
                    ])->find($newPaf->id);

                    $mah = $paf->mah_data;
                    $manufacturerEmail = $mah->contact_email ?? null;

                    if ($manufacturerEmail) {

                        // ALL prescriptions for this indication
                        $patientId = $paf->header->patient_no ?? null;
                        $indicationId = $paf->indication_id;
                        $drugId = $paf->drug_id;

                        $indicationCount = PafDetails::latestVersion()
                            ->where('indication_id', $indicationId)
                            ->whereHas('header', function ($q) use ($patientId) {
                                $q->where('patient_no', $patientId);
                            })
                            ->count();

                        $brandCount = PafDetails::latestVersion()
                            ->where('drug_id', $drugId)
                            ->where('indication_id', $indicationId)
                            ->where('off_label', 1)
                            ->whereHas('header', function ($q) use ($patientId) {
                                $q->where('patient_no', $patientId);
                            })
                            ->count();

                        $emailTemplate = EmailTemplate::where('template_name', 'Off Label Usage Alert')->first();
                        $today = Carbon::today();
                        if ($emailTemplate) {
                            $drugStrengths = collect($request->drug_cycles)
                                ->pluck('drug_strength')
                                ->filter()
                                ->implode(',');

                            $capsules = collect($request->drug_cycles)
                                ->pluck('cap_per_cycle')
                                ->filter()
                                ->implode(',');

                            $userdata = [
                                'manufacturer_name' => $mah->contact_name ?? 'Team',
                                'drug_name' => $paf->drug->drug_name ?? '',
                                'indication' => $paf->indication->name . ' (Under 18)' ?? '',
                                'patient_id' => $paf->header->patient_no ?? '',
                                'paf_no' => $paf->paf_no,
                                'dose' => $drugStrengths,
                                'capsules' => $capsules,
                                'pharmacy_name' => $paf->institutions->pharmacy->name ?? '',
                                'confirmed_off_label' => 'Yes - ' . now()->format('Y-m-d'),
                                'minor_patient_prescription_count' => $indicationCount,
                                'brand_prescription_count' => $brandCount,
                            ];

                            Mail::to($manufacturerEmail)->queue(
                                new RegistrationRejectionMail(
                                    CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata),
                                    CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata),
                                    CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata),
                                    null,
                                    null
                                )
                            );

                            // AUDIT
                            CustomFunctions::audit(
                                module: 'Off Label Usage',
                                action: 'EMAIL SENT',
                                referenceId: $newPaf->id,
                                referenceTable: 'paf_details',
                                oldValues: null,
                                newValues: [
                                    'paf_no' => $paf->paf_no,
                                    'patient_id' => $paf->header->patient_no ?? '',
                                    'drug_name' => $paf->drug->drug_name ?? '',
                                    'manufacturer' => $mah->contact_name ?? '',
                                    'email' => $manufacturerEmail,
                                    'drug_strengths' => $drugStrengths,
                                    'capsules' => $capsules,
                                ],
                                changedFields: ['email', 'drug_strengths', 'capsules'],
                                description: "Off-label alert email sent with multiple drug cycles (Strengths: {$drugStrengths}, Capsules: {$capsules})"
                            );
                        }
                    }
                }

            } catch (\Exception $mailEx) {
                Log::error('Off-label mail failed: ' . $mailEx->getMessage());
            }

            // ================= AUDIT: PAF APPROVED & DISPENSED =================

            CustomFunctions::audit(
                module: 'PAF',
                action: 'APPROVE & DISPENSE',
                referenceId: $newPaf->id,
                referenceTable: 'paf_details',
                oldValues: [
                    'paf_id' => $latest->id,
                    'status' => $latest->status,
                    'version' => $latest->version,
                ],
                newValues: [
                    'paf_id' => $newPaf->id,
                    'status' => $newPaf->status,
                    'version' => $newPaf->version,
                    'dispensing_date' => $newPaf->dispensing_date,
                    'dispensing_by' => $newPaf->dispensing_sig,
                    'drug_strength' => $request->drug_strength,
                    'cap_per_cycle' => $request->cap_per_cycle,
                    'cycles' => $request->cycles,
                    'total_supply' => $request->total_supply,
                    'risk_level' => $newPaf->risk_level ?? 'N/A',
                    'off_label' => $newPaf->off_label ?? 0,
                ],
                changedFields: [
                    'status',
                    'version',
                    'dispensing_date',
                    'drug_cycle',
                ],
                description: "PAF {$newPaf->paf_no} (Version {$latest->version}) has been approved and dispensed. New Version {$newPaf->version} created. Dispensed by {$newPaf->dispensing_sig} on {$newPaf->dispensing_date}. Drug: {$request->drug_strength}, Cycles: {$request->cycles}, Total Supply: {$request->total_supply}. Patient Category: {$newPaf->patient_category}, Risk Level: " . ($newPaf->risk_level ?? 'N/A')
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF Dispensed successfully',
                'data' => $newPaf,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to dispense PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function pafApprove(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            //  Validation
            $request->validate([
                'paf_details_id' => 'required|exists:paf_details,id',

                'drug_cycles' => 'required|array|min:1',
                'drug_cycles.*.drug_strength' => 'required',
                'drug_cycles.*.cap_per_cycle' => 'required',

                'cycles' => 'required',
                'total_supply' => 'required',
                'dispenser_point' => 'required',
                'dispensing_location' => 'required',
            ]);

            // ---------------------------------------------------
            // 1 Get current PAF detail
            // ---------------------------------------------------
            $current = PafDetails::find($request->paf_details_id);

            if (!$current) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            // ---------------------------------------------------
            // 2 Find parent ID (root)
            // ---------------------------------------------------
            $parentId = $current->parent_id ?: $current->id;

            // ---------------------------------------------------
            // 3 Get latest version
            // ---------------------------------------------------
            $latest = PafDetails::where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
            })
                ->orderByDesc('version')
                ->first();

            $newVersion = $latest->version + 1;

            // ---------------------------------------------------
            // 4 Create new PAF DETAILS (CLONE + UPDATE)
            // ---------------------------------------------------
            $newPaf = PafDetails::create([
                'paf_no' => $latest->paf_no,
                'paf_header_id' => $latest->paf_header_id,
                'patient_initials' => $latest->patient_initials,
                'patient_dob' => $latest->patient_dob,
                'prescriber_id' => $latest->prescriber_id,
                'indication_id' => $latest->indication_id,
                'patient_category' => $latest->patient_category,
                'institution_id' => $latest->institution_id,
                'drug_id' => $latest->drug_id,
                'rejection_reason' => $latest->rejection_reason,
                'declaration_date' => $latest->declaration_date,
                'declaration_name' => $latest->declaration_name,

                //  UPDATED VALUES
                'last_negative_preg_date' => $request->last_negative_preg_date,
                'status' => 'Approved',
                'version' => $newVersion,
                'parent_id' => $parentId,

                'admin_notes' => $latest->admin_notes,
                'is_reviewed' => $latest->is_reviewed,
                'off_label' => $latest->off_label,
                'risk_level' => $latest->risk_level,
                'other_indication' => $latest->other_indication,
                'renewal' => $latest->renewal,
                'renewal_paf_parent_id' => $latest->renewal_paf_parent_id,

                //  DISPENSER DETAILS (NEW)
                'dispensing_point' => $request->dispenser_point,
                'dispensing_loc_id' => $request->dispensing_location,
                'dispensing_sig' => $request->dispensing_name,
                'dispensing_date' => $request->dispensing_date,
                'is_retrospective' => $latest->is_retrospective,
                'is_clinical_trial' => $latest->is_clinical_trial,
                'clinical_test_note' => $latest->clinical_test_note,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            PAFConfirmation::create([
                'paf_detail_id' => $parentId,
                'is_confirmed' => $request->risk_confirmed,
                'role' => 'Pharmacist',
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // ---------------------------------------------------
            // 5 LOOP ALL DRUG CYCLES (MULTIPLE ROWS)
            // ---------------------------------------------------

            $drugCycles = $request->drug_cycles ?? [];

            if (empty($drugCycles)) {
                throw new \Exception("Drug cycles data missing");
            }

            foreach ($drugCycles as $index => $cycleData) {

                // Get latest cycle (only once OR reuse same parent)
                $latestCycle = PafDrugCycle::where(function ($q) use ($latest) {
                    $q->where('paf_details_id', $latest->id)
                        ->orWhere('parent_id', $latest->id);
                })
                    ->orderByDesc('version')
                    ->first();

                $cycleParentId = $latestCycle->parent_id ?: $latestCycle->id;

                PafDrugCycle::create([
                    'paf_details_id' => $newPaf->id,

                    'drug_strength' => $cycleData['drug_strength'],
                    'cap_per_cycle' => $cycleData['cap_per_cycle'],

                    'no_of_cycles' => $request->cycles,
                    'total_supply' => $request->total_supply,

                    // keep old
                    'supply_weeks' => $latestCycle->supply_weeks,

                    // versioning
                    'version' => $latestCycle->version + 1,
                    'parent_id' => $cycleParentId,

                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // ---------------------------------------------------
            // 6 APPLY NON-CONFORMANCE RULES (COMMON FUNCTION)
            // ---------------------------------------------------
            CustomFunctions::applyNonConformanceRules(
                $latest,
                $newPaf,
                $request,
                $userId
            );

            // ================= AUDIT: PAF APPROVED =================

            CustomFunctions::audit(
                module: 'PAF',
                action: 'APPROVE',
                referenceId: $newPaf->id,
                referenceTable: 'paf_details',
                oldValues: [
                    'paf_id' => $latest->id,
                    'status' => $latest->status,
                    'version' => $latest->version,
                ],
                newValues: [
                    'paf_id' => $newPaf->id,
                    'status' => $newPaf->status,
                    'version' => $newPaf->version,
                    'dispensing_point' => $newPaf->dispensing_point,
                    'dispensing_location' => $newPaf->dispensing_loc_id,
                    'dispensing_by' => $newPaf->dispensing_sig,
                    'dispensing_date' => $newPaf->dispensing_date,
                    'drug_strength' => $request->drug_strength,
                    'cap_per_cycle' => $request->cap_per_cycle,
                    'cycles' => $request->cycles,
                    'total_supply' => $request->total_supply,
                    'risk_level' => $newPaf->risk_level ?? 'N/A',
                    'off_label' => $newPaf->off_label ?? 0,
                ],
                changedFields: [
                    'status',
                    'version',
                    'dispensing_details',
                    'drug_cycle',
                ],
                description: "PAF {$newPaf->paf_no} (Version {$latest->version}) has been approved. New Version {$newPaf->version} created. Dispensing configured at '{$newPaf->dispensing_point}' (Location ID: {$newPaf->dispensing_loc_id}). Drug: {$request->drug_strength}, Cycles: {$request->cycles}, Total Supply: {$request->total_supply}. Patient Category: {$newPaf->patient_category}, Risk Level: " . ($newPaf->risk_level ?? 'N/A')
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF Approved successfully',
                'data' => $newPaf,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to approve PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to fetch pharmacist count list.
     *
     * @author: Stalvin M
     *
     * @created-on: 22 Mar, 2026
     *
     * @updated-on: 10 April, 2026
     */
    public function getPharmacistPafStats(Request $request)
    {
        $institutionId = $request->institution_id;

        try {

            $institution = Institutions::select('id', 'institution_type')
                ->findOrFail($institutionId);

            $pafQuery = PafHeader::query();

            if ($institution->institution_type === 'Inpatient Pharmacy') {

                $hospitalIds = Institutions::where('pharmacy_id', $institutionId)
                    ->pluck('id');

                $pafQuery->whereHas('pafDetails', function ($q) use ($hospitalIds) {
                    $q->latestVersion()
                        ->whereIn('institution_id', $hospitalIds);
                });

                $pafQuery->with([
                    'pafDetails' => function ($q) use ($hospitalIds) {
                        $q->latestVersion()
                            ->whereIn('institution_id', $hospitalIds);
                    },
                ]);

            } elseif (
                $institution->institution_type === 'Outpatient Pharmacy' ||
                $institution->institution_type === 'Homecare'
            ) {

                $pafQuery->whereHas('pafDetails', function ($q) use ($institutionId) {
                    $q->latestVersion()
                        ->where('dispensing_loc_id', $institutionId)
                        ->where('status', 'Approved');
                });

                $pafQuery->with([
                    'pafDetails' => function ($q) use ($institutionId) {
                        $q->latestVersion()
                            ->where('dispensing_loc_id', $institutionId)
                            ->where('status', 'Approved');
                    },
                ]);
            }

            $pafs = $pafQuery
                ->orderByDesc('created_at')
                ->get();

            $allDetails = $pafs->flatMap(function ($paf) {
                return $paf->pafDetails;
            });

            $totalPafs = $allDetails->count();
            $totalPatients = $pafs->count();
            $activePatients = $allDetails->whereIn('status', ['Action Required', 'Submitted'])->count();
            $overduePaf = $allDetails->where('status', 'Action Required')->count();
            $rejectedPaf = $allDetails->where('status', 'Rejected')->count();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF stats fetched successfully',
                'stats' => [
                    [
                        'label' => 'Total PAFs',
                        'value' => $totalPafs,
                    ],
                    [
                        'label' => 'Total Patients',
                        'value' => $totalPatients,
                    ],
                    [
                        'label' => 'Active Patients',
                        'value' => $activePatients,
                    ],
                    [
                        'label' => 'Overdue PAF (Action Required)',
                        'value' => $overduePaf,
                    ],
                    [
                        'label' => 'Rejected PAF',
                        'value' => $rejectedPaf,
                    ],
                ],
            ]);

        } catch (\Exception $e) {

            Log::error('PAF Stats Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to fetch pharmacist count list.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 23 Mar, 2026
     *
     * @updated-on: N/A
     */

    public function fetchPafHistory(Request $request)
    {
        try {
            $request->validate([
                'paf_id' => 'required|integer',
            ]);

            // Get root ID (parent or self)
            $rootId = PafDetails::where('id', $request->paf_id)
                ->value('parent_id') ?? $request->paf_id;

            // Fetch all versions (latest first)
            $pafs = PafDetails::with('drugCycles')
                ->where(function ($q) use ($rootId) {
                    $q->where('id', $rootId)
                        ->orWhere('parent_id', $rootId);
                })
                ->orderByRaw('COALESCE(version, 0) DESC')
                ->get();

            if ($pafs->isEmpty()) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'No PAF records found',
                    'paf_history' => [],
                ]);
            }

            // GLOBAL FLAG (only one current cycle)
            $isFirstCycle = true;

            $paf_history = [
                'paf_id' => $rootId,
                'versions' => $pafs->values()->map(function ($paf) use (&$isFirstCycle) {

                    return [
                        'id' => $paf->id,
                        'paf_no' => $paf->paf_no,
                        'version' => $paf->version,
                        'status' => $paf->status,

                        'patient_initials' => $paf->patient_initials,
                        'patient_dob' => $paf->patient_dob,
                        'patient_category' => $paf->patient_category,

                        'declaration_date' => $paf->declaration_date,
                        'declaration_name' => $paf->declaration_name,

                        'drug_cycles' => $paf->drugCycles
                            ->sortByDesc(function ($cycle) {
                                return $cycle->version ?? 0;
                            })
                            ->values()
                            ->map(function ($cycle) use ($paf, &$isFirstCycle) {

                                $isCurrent = false;

                                // ONLY FIRST CYCLE ACROSS ALL VERSIONS
                                if ($isFirstCycle) {
                                    $isCurrent = true;
                                    $isFirstCycle = false;
                                }

                                return [
                                    'id' => $cycle->id,
                                    'drug_strength' => $cycle->drug_strength,
                                    'cap_per_cycle' => $cycle->cap_per_cycle,
                                    'no_of_cycles' => $cycle->no_of_cycles,
                                    'supply_weeks' => $cycle->supply_weeks,
                                    'total_supply' => $cycle->total_supply,
                                    'version' => $cycle->version,

                                    'is_current' => $isCurrent,

                                    'patient_category' => $paf->patient_category,
                                    'last_negative_preg_date' => $paf->last_negative_preg_date,
                                ];
                            }),
                    ];
                }),
            ];

            return response()->json([
                'status' => 'S',
                'message' => 'PAF history grouped successfully',
                'paf_history' => $paf_history,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to fetch pharmacist count list.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 23 Mar, 2026
     *
     * @updated-on: N/A
     */

    public function getPafByDetailsId(Request $request)
    {
        try {

            $request->validate([
                'paf_id' => 'required|exists:paf_details,id',
            ]);

            $pafDetails = PafDetails::with([
                'indication:id,name',
                'drug:id,drug_name,drug_form',
                'prescriber' => function ($q) {
                    $q->select('id', 'name', 'lastname')
                        ->with('prescriber_data');
                },
                'drugCycles' => function ($q) {
                    $q->latestVersion();
                },
                'header:id,patient_no,gender,paf_status',
            ])
                ->where('id', $request->paf_id)
                ->first();

            if (!$pafDetails) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => 'PAF fetched successfully',
                'paf_details' => $pafDetails,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: To revert a paf.
     *
     * @author: Stalvin
     *
     * @created-on: 03 Mar, 2026
     *
     * @updated-on: 03 Mar, 2026
     */
    public function pafRevert(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $request->validate([
                'paf_details_id' => 'required|exists:paf_details,id',
                'reason' => 'required|string|max:255',
            ]);

            // ---------------------------------------------------
            // 1 Get current PAF
            // ---------------------------------------------------
            $current = PafDetails::find($request->paf_details_id);

            if (!$current) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            // Optional safety
            if ($current->status === 'Reverted') {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF already reverted',
                ]);
            }

            // ---------------------------------------------------
            // 2 Find parent ID (root)
            // ---------------------------------------------------
            $parentId = $current->parent_id ?: $current->id;

            // ---------------------------------------------------
            // 3 Get latest version
            // ---------------------------------------------------
            $latest = PafDetails::where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
            })
                ->orderByDesc('version')
                ->first();

            $newVersion = $latest->version + 1;

            // ---------------------------------------------------
            // 4 Create NEW VERSION (Reverted)
            // ---------------------------------------------------
            $newPaf = PafDetails::create([
                'paf_no' => $latest->paf_no,
                'paf_header_id' => $latest->paf_header_id,
                'patient_initials' => $latest->patient_initials,
                'patient_dob' => $latest->patient_dob,
                'last_negative_preg_date' => $latest->last_negative_preg_date,
                'prescriber_id' => $latest->prescriber_id,
                'indication_id' => $latest->indication_id,
                'other_indication' => Str::title($latest->other_indication),
                'patient_category' => $latest->patient_category,
                'institution_id' => $latest->institution_id,
                'drug_id' => $latest->drug_id,

                // revert fields
                'revert_reason' => $request->reason,
                'status' => 'Reverted',

                // versioning
                'version' => $newVersion,
                'parent_id' => $parentId,

                'admin_notes' => $latest->admin_notes,
                'is_reviewed' => $latest->is_reviewed,
                'off_label' => $latest->off_label,
                'risk_level' => $latest->risk_level,

                // carry forward
                'declaration_date' => $latest->declaration_date,
                'declaration_name' => $latest->declaration_name,
                'is_retrospective' => $latest->is_retrospective,
                'is_clinical_trial' => $latest->is_clinical_trial,
                'clinical_test_note' => $latest->clinical_test_note,

                // clear dispensing fields (since reverting)
                'dispensing_point' => null,
                'dispensing_loc_id' => null,
                'dispensing_sig' => null,
                'dispensing_date' => null,

                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // ---------------------------------------------------
            // 4.1 Other Revert Rule
            // ---------------------------------------------------

            if ($request->is_other_revert == 1) {

                CustomFunctions::createNonConformance(
                    $newPaf->id,
                    'OTHER_REVERT_REVIEW',
                    $userId
                );

                // Update risk level based on patient category
                if ($newPaf->patient_category === 'WCBP') {

                    $newPaf->update([
                        'risk_level' => 'High Risk',
                    ]);

                } elseif ($newPaf->patient_category === 'WNCBP') {

                    $newPaf->update([
                        'risk_level' => 'Low Risk',
                    ]);
                }
            }

            // ---------------------------------------------------
            // 5 Clone latest DRUG CYCLE
            // ---------------------------------------------------

            $latestVersion = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })->max('version');

            $latestCycles = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })
                ->where('version', $latestVersion)
                ->get();

            if ($latestCycles->isEmpty()) {
                throw new \Exception("No drug cycles found");
            }

            foreach ($latestCycles as $cycle) {

                $cycleParentId = $cycle->parent_id ?: $cycle->id;

                PafDrugCycle::create([
                    'paf_details_id' => $newPaf->id,

                    'drug_strength' => $cycle->drug_strength,
                    'cap_per_cycle' => $cycle->cap_per_cycle,
                    'no_of_cycles' => $cycle->no_of_cycles,
                    'total_supply' => $cycle->total_supply,
                    'supply_weeks' => $cycle->supply_weeks,

                    'version' => $cycle->version + 1,
                    'parent_id' => $cycleParentId,

                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            // ================= AUDIT =================
            try {

                $paf = PafDetails::with(['drug', 'header'])->find($newPaf->id);

                $oldValues = [
                    'paf_no' => $latest->paf_no,
                    'version' => $latest->version,
                    'status' => $latest->status,
                    'drug_id' => $latest->drug_id,
                    'patient_id' => $paf->header->patient_no ?? null,
                    'revert_reason' => null,
                ];

                $newValues = [
                    'paf_no' => $newPaf->paf_no,
                    'version' => $newPaf->version,
                    'status' => 'Reverted',
                    'drug_id' => $newPaf->drug_id,
                    'patient_id' => $paf->header->patient_no ?? null,
                    'revert_reason' => $request->reason,
                ];

                // Copy non-conformances from old to new
                CustomFunctions::copyNonConformances(
                    $latest->id,
                    $newPaf->id,
                    $userId
                );

                CustomFunctions::audit(
                    module: 'PAF',
                    action: 'REVERT',
                    referenceId: $newPaf->id,
                    referenceTable: 'paf_details',
                    oldValues: $oldValues,
                    newValues: $newValues,
                    changedFields: ['status', 'version', 'revert_reason'],
                    description:
                    'PAF Reverted: PAF No ' . $newPaf->paf_no .
                    ' (Version ' . $latest->version . ' → ' . $newPaf->version . ') has been reverted. ' .
                    'Reason: "' . $request->reason . '". ' .
                    'Patient ID: ' . ($paf->header->patient_no ?? 'N/A') . ', ' .
                    'Drug: ' . ($paf->drug->drug_name ?? 'N/A') . '.'
                );

            } catch (\Exception $auditEx) {
                Log::error('PAF Revert Audit Failed: ' . $auditEx->getMessage());
            }
            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF reverted successfully',
                'data' => $newPaf,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to revert PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @function: To get all PAFs detail.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 01 Apr, 2026
     *
     * @updated-on: 01 Apr, 2026
     */
    public function getAllPafs(Request $request)
    {

        $institutionId = $request->institution_id;
        try {

            $userData = Auth::user();

            if ($userData->rolename == "Nurse") {
                $userId = $userData->created_user->id;
            } else {
                $userId = auth()->id();
            }

            $pafs = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion()
                        ->with([
                            'indication:id,name',
                            'drug:id,drug_name,drug_form',
                            'institutions:id,name',
                            'prescriber' => function ($q) {
                                $q->select('id', 'name', 'lastname')
                                    ->with('prescriber_data');
                            },
                            'drugCycles' => function ($q) {
                                $q->latestVersion();
                            },
                        ]);
                },
            ])

                ->orderBy('created_at', 'desc')
                ->get();

            $formatted = $pafs->map(function ($paf) {

                $details = $paf->pafDetails->first();

                return [
                    'id' => $paf->id,
                    'patient_id' => $paf->patient_no,
                    'patient_initials' => $details->patient_initials ?? null,
                    'dob' => $details->patient_dob ?? null,
                    'gender' => $paf->gender ?? null,
                    'patient_category' => $details->patient_category ?? null,
                    'indication' => $details->indication->name ?? null,
                    'drug_name' => $details->drug->drug_name ?? null,
                    'is_retrospective' => $details->is_retrospective ?? null,
                    'is_clinical_trial' => $details->is_clinical_trial ?? null,
                    'clinical_test_note' => $details->clinical_test_note ?? null,

                    'prescriber' => $details && $details->prescriber
                    ? $details->prescriber->name . ' ' . $details->prescriber->lastname
                    : null,
                    'paf_status' => $paf->paf_status,
                    'slug' => $paf->slug,
                    'created_at' => $paf->created_at,

                    // COUNT
                    'paf_details_count' => $paf->pafDetails->count(),
                    //Institution_data
                    'institution_data' => $details->institutions,
                    'non_conformance' => $details->non_conformance,

                    // ARRAY OF PAF DETAILS
                    'paf_details_list' => $paf->pafDetails->map(function ($detail) {
                        return [
                            'paf_no' => $detail->paf_no ?? null,
                            'declaration_date' => $detail->declaration_date ?? null,
                            'declaration_name' => $detail->declaration_name ?? null,
                            'status' => $detail->status ?? null,
                        ];
                    })->values(),
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'PAFs fetched successfully',
                'data' => $formatted,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch PAFs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @function: to fetch both pharmacist & Prescriber count list.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 02 April, 2026
     *
     * @updated-on: N/A
     */
    public function getAllPafStats(Request $request)
    {
        try {

            $pafs = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion();
                },
            ])
                ->orderBy('created_at', 'desc')
                ->get();

            // Flatten all paf_details
            $allDetails = $pafs->flatMap(function ($paf) {
                return $paf->pafDetails;
            });

            // Counts
            $totalPafs = $allDetails->count(); // all PAF entries
            $totalPatients = $pafs->count(); // unique headers
            $overduePaf = $allDetails->where('status', 'Action Required')->count();
            $rejectedPaf = $allDetails->where('status', 'Rejected')->count();
            $activePatients = $allDetails->whereIn('status', ['Action Required', 'Submitted'])->count();

            return response()->json([
                'status' => 'S',
                'message' => 'PAF stats fetched successfully',
                'stats' => [
                    [
                        'label' => 'Total PAFs',
                        'value' => $totalPafs,
                    ],
                    [
                        'label' => 'Total Patients',
                        'value' => $totalPatients,
                    ],
                    [
                        'label' => 'Active Patients',
                        'value' => $activePatients,
                    ],
                    [
                        'label' => 'Overdue PAF (Action Required)',
                        'value' => $overduePaf,
                    ],
                    [
                        'label' => 'Rejected PAF',
                        'value' => $rejectedPaf,
                    ],
                ],
                'data' => $pafs,
            ]);

        } catch (\Exception $e) {

            Log::error('PAF Stats Error', ['error' => $e->getMessage()]);

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to fetch both pharmacist & Prescriber count list.
     *
     * @author: Stalvin
     *
     * @created-on: 02 April, 2026
     *
     * @updated-on: N/A
     */

    public function getAllPafDetails(Request $request)
    {
        try {

            $slug = $request->slug;

            $paf = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion()->with([
                        'indication:id,name',
                        'institutions:id,name,institution_type,address,pharmacy_id',
                        'drug:id,drug_name,drug_form',
                        'mah_data:id,contact_name,contact_email,logo',
                        'dispensing_loc:id,name,ref_number',
                        'prescriber' => function ($q) {
                            $q->select('id', 'name', 'lastname')
                                ->with('prescriber_data');
                        },
                        'drugCycles' => function ($q) {
                            $q->latestVersion();
                        },
                    ]);
                },
            ])
                ->where('slug', $slug)
                ->first();

            if (!$paf) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            // ================= OVERDUE LOGIC =================
            $overdueParam = SystemParameters::where('parameter_name', 'PAF_OVERDUE_TIME')
                ->where('status', 1)
                ->first();

            $overdueTime = $overdueParam->parameter_value ?? '00:00';

            $timeParts = explode(':', $overdueTime);
            $hour = (int) ($timeParts[0] ?? 0);
            $minute = (int) ($timeParts[1] ?? 0);

            $timezone = config('app.timezone');
            $now = Carbon::now($timezone);

            foreach ($paf->pafDetails as $detail) {

                $declarationDate = $detail->declaration_date
                ? Carbon::parse($detail->declaration_date, $timezone)
                : null;

                $isOverdue = false;
                $overdueText = null;

                if ($detail->is_reviewed == 0 && $declarationDate) {

                    $deadline = $declarationDate->copy()
                        ->addDay()
                        ->addHours($hour)
                        ->addMinutes($minute);

                    if ($now->greaterThan($deadline)) {
                        $isOverdue = true;
                        $overdueText = 'PAF is overdue. Please review it.';
                    }
                }

                $detail->is_overdue = $isOverdue;
                $detail->overdue_text = $overdueText;

                // ================= REQUEST USERS =================
                $requestUsers = [];

                // Prescriber
                if ($detail->prescriber) {
                    $requestUsers[] = [
                        'id' => $detail->prescriber->id,
                        'name' => $detail->prescriber->full_name,
                        'role' => 'Prescriber',
                    ];
                }

                // Pharmacist & Lead Pharmacist
                if ($detail->institution_id) {

                    $pharmacists = \App\Models\PharmacistDetails::with('user')
                        ->where('institution_id', $detail->institutions->pharmacy_id)
                        ->get();

                    Log::info('pharmacists===========');
                    Log::info($pharmacists);

                    foreach ($pharmacists as $pharma) {

                        if (!empty($pharma->user)) {
                            $requestUsers[] = [
                                'id' => $pharma->user->id,
                                'name' => $pharma->user->full_name,
                                'role' => $pharma->role === 'Lead Pharmacist'
                                ? 'Lead Pharmacist'
                                : 'Pharmacist',
                            ];
                        }
                    }
                }

                $requestCount = PafRequestInformation::where('paf_detail_id', $detail->id)->count();

                $detail->request_count = $requestCount;

                $detail->request_message = $requestCount > 0
                ? "You have already created {$requestCount} request" . ($requestCount > 1 ? 's' : '') . " for this PAF."
                : null;

                $requestRecords = PafRequestInformation::where('paf_detail_id', $detail->id)
                    ->orderBy('created_at', 'desc')
                    ->get([
                        'id',
                        'paf_no',
                        'patient_id',
                        'request_note',
                        'requested_users',
                        'reminder_count',
                        'created_at',
                    ]);

                $detail->request_history = $requestRecords->map(function ($rec) {
                    return [
                        'id' => $rec->id,
                        'paf_no' => $rec->paf_no,
                        'patient_id' => $rec->patient_id,
                        'note' => $rec->request_note,
                        'reminders' => $rec->reminder_count,
                        'created_at' => $rec->created_at->format('d M Y h:i A'),
                    ];
                });

                $detail->request_users = $requestUsers;
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Data fetched successfully.',
                'paf' => $paf,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'F',
                'message' => 'Failed to fetch PAF details',
                'error' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: Review PAF both single and bluk.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 08 April, 2026
     *
     * @updated-on: N/A
     */

    public function bulkPAFReview(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $request->validate([
                'paf_ids' => 'required|array|min:1',
                'paf_ids.*' => 'exists:paf_details,id',
                'is_reviewed' => 'required|boolean',
            ]);

            // ---------------------------------------
            // 1 GET OLD DATA (FOR AUDIT)
            // ---------------------------------------
            $oldRecords = PafDetails::whereIn('id', $request->paf_ids)
                ->get()
                ->toArray();

            // ---------------------------------------
            // 2 BULK UPDATE
            // ---------------------------------------
            $updated = PafDetails::whereIn('id', $request->paf_ids)
                ->update([
                    'is_reviewed' => $request->is_reviewed,
                    'admin_notes' => $request->admin_notes ?: null,
                    'updated_by' => $userId,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'No PAFs updated',
                ]);
            }

            DB::commit();

            // ---------------------------------------
            // 3 PREPARE NEW DATA
            // ---------------------------------------
            $newRecords = collect($oldRecords)->map(function ($item) use ($request, $userId) {
                $item['is_reviewed'] = $request->is_reviewed;
                $item['admin_notes'] = $request->admin_notes ?: null;
                $item['updated_by'] = $userId;
                $item['updated_at'] = now();
                return $item;
            })->toArray();

            // ---------------------------------------
            // 4 AUDIT (AFTER COMMIT)
            // ---------------------------------------
            try {

                CustomFunctions::audit(
                    module: 'PAF',
                    action: 'BULK REVIEW UPDATE',
                    referenceId: null,
                    referenceTable: 'paf_details',
                    oldValues: $oldRecords,
                    newValues: $newRecords,
                    changedFields: ['is_reviewed', 'admin_notes'],
                    description:
                    'Bulk PAF Review Update: ' . count($request->paf_ids) . ' PAF record(s) updated. ' .
                    'Review Status: "' . ($request->is_reviewed ? 'Reviewed' : 'Not Reviewed') . '". ' .
                    (!empty($request->admin_notes)
                        ? 'Admin Notes: "' . $request->admin_notes . '". '
                        : 'No admin notes provided. '
                    ) .
                    'Affected PAF IDs: [' . implode(', ', $request->paf_ids) . ']'
                );

            } catch (\Exception $auditEx) {
                \Log::error('Bulk PAF Review Audit Failed: ' . $auditEx->getMessage());
            }

            return response()->json([
                'status' => 'S',
                'message' => count($request->paf_ids) > 1
                ? 'Bulk review updated successfully'
                : 'Review updated successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to update review',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function mergePaf(Request $request)
    {
        $data = $request->all();

        DB::beginTransaction();

        try {

            if (empty($data['patient_ids']) || count($data['patient_ids']) < 2) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'At least two patient IDs are required to merge.',
                ]);
            }

            $patientIds = array_unique($data['patient_ids']);

            // ---------------------------------------------------
            // 1 Fetch PAF Headers
            // ---------------------------------------------------

            $headers = PafHeader::whereIn('patient_no', $patientIds)->get();

            if ($headers->count() !== count($patientIds)) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'One or more patient IDs are invalid.',
                ]);
            }

            // ---------------------------------------------------
            // 2 Fetch latest PAF Details for each header
            // ---------------------------------------------------

            $latestPafs = [];

            foreach ($headers as $header) {

                $paf = PafDetails::where('paf_header_id', $header->id)
                    ->latestVersion()
                    ->first();

                if (!$paf) {
                    return response()->json([
                        'status' => 'E',
                        'message' => 'No PAF details found for patient ' . $header->patient_no,
                    ]);
                }

                $latestPafs[] = $paf;
            }

            // ---------------------------------------------------
            // 3 Validate patient fields are same
            // ---------------------------------------------------

            $first = $latestPafs[0];

            foreach ($latestPafs as $paf) {

                if (
                    $paf->patient_initials !== $first->patient_initials ||
                    $paf->patient_dob !== $first->patient_dob ||
                    $paf->patient_category !== $first->patient_category ||
                    $paf->institution_id !== $first->institution_id
                ) {

                    return response()->json([
                        'status' => 'E',
                        'message' => 'Selected patients cannot be merged because patient details do not match (Initials, DOB, Category or Institution).',
                    ]);
                }
            }

            // ---------------------------------------------------
            // 4 Only ONE submitted PAF allowed
            // (latest version of every PAF)
            // ---------------------------------------------------

            $headerIds = $headers->pluck('id');

            $latestPafRecords = PafDetails::latestVersion()
                ->whereIn('paf_header_id', $headerIds)
                ->get();

            $submittedCount = $latestPafRecords
                ->where('status', 'Submitted')
                ->count();

            if ($submittedCount > 1) {

                return response()->json([
                    'status' => 'E',
                    'message' => 'There can only be one submitted prescription among the selected patients.',
                ]);
            }

            // ---------------------------------------------------
            // 5 Identify PRIMARY patient (oldest header)
            // ---------------------------------------------------

            $primaryHeader = $headers
                ->sortBy('created_at')
                ->first();

            $primaryPatientNo = $primaryHeader->patient_no;

            // ---------------------------------------------------
            // 6 Identify duplicate headers
            // ---------------------------------------------------

            $duplicateHeaders = $headers
                ->where('id', '!=', $primaryHeader->id);

            $mergedPatients = [];

            foreach ($duplicateHeaders as $duplicateHeader) {

                $mergedPatients[] = $duplicateHeader->patient_no;

                // ---------------------------------------------------
                // Move all paf_details to primary header
                // ---------------------------------------------------

                PafDetails::where('paf_header_id', $duplicateHeader->id)
                    ->update([
                        'paf_header_id' => $primaryHeader->id,
                    ]);

                // ---------------------------------------------------
                // Delete duplicate header
                // ---------------------------------------------------

                $duplicateHeader->delete();
            }

            // ---------------------------------------------------
            // Success Message
            // ---------------------------------------------------

            $mergedList = implode(', ', $mergedPatients);

            DB::commit();

            try {

                // Prepare OLD values (before merge)
                $oldValues = [
                    'primary_patient' => null,
                    'merged_patients' => $patientIds,
                    'total_patients' => count($patientIds),
                ];

                // Prepare NEW values (after merge)
                $newValues = [
                    'primary_patient' => $primaryPatientNo,
                    'merged_patients' => $mergedPatients,
                    'total_patients' => count($patientIds),
                ];

                CustomFunctions::audit(
                    module: 'PAF',
                    action: 'MERGE',
                    referenceId: $primaryHeader->id,
                    referenceTable: 'paf_headers',
                    oldValues: $oldValues,
                    newValues: $newValues,
                    changedFields: ['paf_header_id'],
                    description:
                    'PAF Merge Operation: Multiple patient records have been merged into a single primary patient. ' .
                    'Primary Patient ID: ' . $primaryPatientNo . '. ' .
                    'Merged Patient IDs: [' . implode(', ', $mergedPatients) . ']. ' .
                    'Total Records Merged: ' . count($mergedPatients) . '. ' .
                    'All associated PAF records from duplicate patients have been reassigned to the primary patient and duplicate records have been removed.'
                );

            } catch (\Exception $auditEx) {
                Log::error('PAF Merge Audit Failed: ' . $auditEx->getMessage());
            }

            return response()->json([
                'status' => 'S',
                'message' => "Patient records {$mergedList} merged into {$primaryPatientNo} successfully.",
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to merge PAF.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkExistingActivePaf(Request $request)
    {
        try {

            $initials = $request->patient_initials;
            $dob = $request->dob;
            $category = $request->patient_category;
            $institutionId = $request->institution_id;

            $exists = PafHeader::whereHas('pafDetails', function ($q) use ($initials, $dob, $category, $institutionId) {

                $q->latestVersion()
                    ->where('patient_initials', $initials)
                    ->whereDate('patient_dob', $dob)
                    ->where('patient_category', $category)
                    ->where('institution_id', $institutionId)
                    ->where('status', 'Submitted');

            })->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'E',
                    'exists' => true,
                    'message' => 'There can only be one submitted prescription for a patient.',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'exists' => false,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to check active PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @function: To get all PAFs detail.
     *
     * @author: Santhosha G
     *
     * @created-on: 01 Apr, 2026
     *
     * @updated-on: 01 Apr, 2026
     */
    public function getOffLablePafs(Request $request)
    {
        try {

            $pafs = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion()
                        ->where('off_label', 1)
                        ->with([
                            'indication:id,name',
                            'drug:id,drug_name,drug_form',
                            'institutions:id,name',
                            'prescriber' => function ($q) {
                                $q->select('id', 'name', 'lastname')
                                    ->with('prescriber_data');
                            },
                            'drugCycles' => function ($q) {
                                $q->latestVersion();
                            },
                        ]);
                },
            ])
                ->whereHas('pafDetails', function ($q) {
                    $q->latestVersion()->where('off_label', 1);
                })
                ->orderBy('created_at', 'desc')
                ->get();

            $formatted = $pafs->map(function ($paf) {

                $details = $paf->pafDetails->first();

                return [
                    'id' => $paf->id,
                    'patient_id' => $paf->patient_no,
                    'patient_initials' => $details?->patient_initials,
                    'dob' => $details?->patient_dob,
                    'gender' => $paf->gender,
                    'indication' => $details?->indication?->name,
                    'drug_name' => $details?->drug?->drug_name,
                    'prescriber' => $details?->prescriber
                    ? $details->prescriber->name . ' ' . $details->prescriber->lastname
                    : null,
                    'paf_status' => $paf->paf_status,
                    'slug' => $paf->slug,
                    'created_at' => $paf->created_at,
                    'non_conformance' => $details->non_conformance,

                    // COUNT
                    'paf_details_count' => $paf->pafDetails->count(),

                    // Institution
                    'institution_data' => $details?->institutions ?? null,

                    // DETAILS LIST
                    'paf_details_list' => $paf->pafDetails->map(function ($detail) {
                        return [
                            'paf_no' => $detail->paf_no,
                            'declaration_date' => $detail->declaration_date,
                            'declaration_name' => $detail->declaration_name,
                            'status' => $detail->status,
                        ];
                    })->values(),
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'Off-label PAFs fetched successfully',
                'data' => $formatted,
            ]);

        } catch (\Exception $e) {

            Log::error($e);

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch PAFs',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @function: To store request PAFs information and sending mail.
     *
     * @author: Santhosha G
     *
     * @created-on: 15 Apr, 2026
     *
     * @updated-on: N/A
     */
    public function sendPafRequestInfo(Request $request)
    {
        DB::beginTransaction();
        $userId = auth()->id();

        try {

            $request->validate([
                'paf_detail_id' => 'required|exists:paf_details,id',
                'user_ids' => 'required|array',
                'request_note' => 'required|string',
            ]);

            $paf = PafDetails::with(['drug', 'institutions'])->find($request->paf_detail_id);

            // Save request
            $record = PafRequestInformation::create([
                'paf_detail_id' => $request->paf_detail_id,
                'paf_no' => $request->paf_no,
                'patient_id' => $request->patient_id,
                'request_note' => $request->request_note,
                'requested_users' => $request->user_ids,
                'reminder_count' => 0,
                'created_by' => Auth::id(),
            ]);

            CustomFunctions::audit(
                module: 'PAF',
                action: 'REQUEST INFO',
                referenceId: $record->id,
                referenceTable: 'paf_request_information',
                oldValues: null,
                newValues: [
                    'paf_detail_id' => $record->paf_detail_id,
                    'paf_no' => $record->paf_no,
                    'patient_id' => $record->patient_id,
                    'requested_users' => $request->user_ids,
                    'request_note' => $record->request_note,
                ],
                changedFields: ['paf_detail_id', 'requested_users', 'request_note'],
                description:
                'PAF Additional Information Requested: PAF No ' . $record->paf_no .
                ' for Patient ID ' . $record->patient_id . ' requires additional information. ' .
                'Request Note: "' . $record->request_note . '". ' .
                'Requested from User IDs: [' . implode(', ', $request->user_ids) . ']. ' .
                'Total Users Notified: ' . count($request->user_ids) . '.'
            );

            // Fetch users
            $users = User::whereIn('id', $request->user_ids)->get();

            // Email Template
            $emailTemplate = EmailTemplate::where('template_name', 'PAF Additional Information Request')->first();

            foreach ($users as $user) {

                if (
                    $emailTemplate &&
                    ($emailTemplate->is_mandatory == 1 ||
                        ($emailTemplate->is_mandatory == 0 && $user->email_subscription == 1))
                ) {

                    $userdata = [
                        'firstname' => $user->full_name,
                        'paf_no' => $paf->paf_no,
                        'patient_id' => $paf->header->patient_no ?? '',
                        'drug_name' => $paf->drug->drug_name ?? '',
                        'institution' => $paf->institutions->name ?? '',
                        'request_note' => $request->request_note,
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
                'message' => 'Request sent successfully',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'E',
                'message' => 'Failed to send request',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function pafOPDispense(Request $request)
    {
        DB::beginTransaction();

        try {

            $userId = auth()->id();

            $request->validate([
                'paf_details_id' => 'required|exists:paf_details,id',
            ]);

            // ---------------------------------------------------
            // 1 Get current PAF detail
            // ---------------------------------------------------

            $current = PafDetails::find($request->paf_details_id);

            if (!$current) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'PAF not found',
                ]);
            }

            // ---------------------------------------------------
            // 2 Find parent ID (root)
            // ---------------------------------------------------

            $parentId = $current->parent_id ?: $current->id;

            // ---------------------------------------------------
            // 3 Get latest version
            // ---------------------------------------------------

            $latest = PafDetails::where(function ($q) use ($parentId) {
                $q->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
            })
                ->orderByDesc('version')
                ->first();

            $newVersion = $latest->version + 1;

            // ---------------------------------------------------
            // 4 Create new PAF DETAILS (CLONE)
            // ---------------------------------------------------

            $newPaf = PafDetails::create([
                'paf_no' => $latest->paf_no,
                'paf_header_id' => $latest->paf_header_id,
                'patient_initials' => $latest->patient_initials,
                'patient_dob' => $latest->patient_dob,
                'prescriber_id' => $latest->prescriber_id,
                'indication_id' => $latest->indication_id,
                'patient_category' => $latest->patient_category,
                'institution_id' => $latest->institution_id,
                'drug_id' => $latest->drug_id,
                'rejection_reason' => $latest->rejection_reason,
                'declaration_date' => $latest->declaration_date,
                'declaration_name' => $latest->declaration_name,

                'last_negative_preg_date' => $latest->last_negative_preg_date,
                'status' => 'Dispensed',

                'version' => $newVersion,
                'parent_id' => $parentId,

                'mah_id' => $latest->mah_id,
                'is_inpatient' => 0, // OP dispense
                'dispensing_sig' => $latest->dispensing_sig,
                'dispensing_date' => now(),

                'admin_notes' => $latest->admin_notes,
                'is_reviewed' => $latest->is_reviewed,
                'off_label' => $latest->off_label,
                'risk_level' => $latest->risk_level,
                'other_indication' => $latest->other_indication,
                'renewal' => $latest->renewal,
                'renewal_paf_parent_id' => $latest->renewal_paf_parent_id,
                'is_retrospective' => $latest->is_retrospective,
                'is_clinical_trial' => $latest->is_clinical_trial,
                'clinical_test_note' => $latest->clinical_test_note,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // Copy non-conformances from previous version
            CustomFunctions::copyNonConformances(
                $latest->id,
                $newPaf->id,
                $userId
            );

            // ---------------------------------------------------
            // 5 Get latest DRUG CYCLE
            // ---------------------------------------------------

            $latestVersion = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })->max('version');

            $latestCycles = PafDrugCycle::where(function ($q) use ($latest) {
                $q->where('paf_details_id', $latest->id)
                    ->orWhere('parent_id', $latest->id);
            })
                ->where('version', $latestVersion)
                ->get();

            if ($latestCycles->isEmpty()) {
                throw new \Exception("No drug cycles found");
            }

            // ---------------------------------------------------
            // 6 Create new DRUG CYCLE (CLONE)
            // ---------------------------------------------------

            foreach ($latestCycles as $cycle) {

                $cycleParentId = $cycle->parent_id ?: $cycle->id;

                PafDrugCycle::create([
                    'paf_details_id' => $newPaf->id,

                    'drug_strength' => $cycle->drug_strength,
                    'cap_per_cycle' => $cycle->cap_per_cycle,
                    'no_of_cycles' => $cycle->no_of_cycles,
                    'total_supply' => $cycle->total_supply,
                    'supply_weeks' => $cycle->supply_weeks,

                    'version' => $cycle->version + 1,
                    'parent_id' => $cycleParentId,

                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]);
            }

            DB::commit();

            try {

                $paf = PafDetails::with(['drug', 'header'])->find($newPaf->id);

                // OLD VALUES
                $oldValues = [
                    'paf_no' => $latest->paf_no,
                    'version' => $latest->version,
                    'status' => $latest->status,
                    'patient_id' => $paf->header->patient_no ?? null,
                    'drug_id' => $latest->drug_id,
                    'dispense_type' => $latest->is_inpatient ? 'IP' : 'OP',
                ];

                // NEW VALUES
                $newValues = [
                    'paf_no' => $newPaf->paf_no,
                    'version' => $newPaf->version,
                    'status' => 'Dispensed',
                    'patient_id' => $paf->header->patient_no ?? null,
                    'drug_id' => $newPaf->drug_id,
                    'dispense_type' => 'OP',
                    'dispensing_date' => $newPaf->dispensing_date,
                ];

                CustomFunctions::audit(
                    module: 'PAF',
                    action: 'DISPENSE (OP)',
                    referenceId: $newPaf->id,
                    referenceTable: 'paf_details',
                    oldValues: $oldValues,
                    newValues: $newValues,
                    changedFields: ['status', 'version', 'dispense_type'],
                    description:
                    'PAF Outpatient Dispensed: PAF No ' . $newPaf->paf_no .
                    ' has been dispensed as Outpatient (OP). ' .
                    'Version updated from ' . $latest->version . ' to ' . $newPaf->version . '. ' .
                    'Previous Status: ' . $latest->status . ', Current Status: Dispensed. ' .
                    'Patient ID: ' . ($paf->header->patient_no ?? 'N/A') . ', ' .
                    'Drug: ' . ($paf->drug->drug_name ?? 'N/A') . '. ' .
                    'Dispensing Date: ' . \Carbon\Carbon::parse($newPaf->dispensing_date)->format('d-m-Y h:i A') . '.'
                );

            } catch (\Exception $auditEx) {
                Log::error('PAF OP Dispense Audit Failed: ' . $auditEx->getMessage());
            }

            return response()->json([
                'status' => 'S',
                'message' => 'PAF Dispensed successfully',
                'data' => $newPaf,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to dispense PAF',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to fetch all paf counts.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 16 April, 2026
     *
     * @updated-on: N/A
     */
    public function getAllPafCounts(Request $request)
    {
        try {

            $pafs = PafHeader::with([
                'pafDetails' => function ($query) {
                    $query->latestVersion();
                },
            ])
                ->orderBy('created_at', 'desc')

                ->get();

            /*
            |--------------------------------------------------------------------------
            | Flatten all paf details
            |--------------------------------------------------------------------------
             */
            $allDetails = $pafs->flatMap(function ($paf) {
                return $paf->pafDetails ?? collect();
            })->values();

            /*
            |--------------------------------------------------------------------------
            | Total PAFs
            |--------------------------------------------------------------------------
             */
            $totalPafs = $allDetails->count();

            /*
            |--------------------------------------------------------------------------
            | PAFs Created Within 7 Days
            |--------------------------------------------------------------------------
             */
            $wcbpDetails = $allDetails
                ->where('patient_category', 'WCBP')
                ->whereNotNull('declaration_date')
                ->values();

            $totalWithin7Days = $wcbpDetails->filter(function ($item) {

                if (!$item->declaration_date) {
                    return false;
                }

                return \Carbon\Carbon::parse($item->declaration_date)
                    ->gte(now()->subDays(7));

            })->count();

            /*
            |--------------------------------------------------------------------------
            | Only WCBP records
            |--------------------------------------------------------------------------
             */

            $totalWcbp = $wcbpDetails->count();

            // dispensed count
            $wcbpDetailsDispensed = $allDetails
                ->where('patient_category', 'WCBP')
                ->where('status', 'Dispensed')
                ->values();

            $totalWcbpdispensed = $wcbpDetailsDispensed->count();

            // Log::info('$totalWcbp');
            // Log::info($totalWcbp);

            /*
            |--------------------------------------------------------------------------
            | Dispensed within 7 days
            |--------------------------------------------------------------------------
             */
            $dispensedWithin7Days = $wcbpDetailsDispensed->filter(function ($item) {

                if ($item->status !== 'Dispensed') {
                    return false;
                }

                if (!$item->dispensing_date) {
                    return false;
                }

                $dispensedDate = \Carbon\Carbon::parse($item->dispensing_date)->startOfDay();
                $today = \Carbon\Carbon::today();

                return $dispensedDate <= $today &&
                $dispensedDate->diffInDays($today) <= 7;

            })->count();

            $dispensedWithin7DaysPercentage = $totalWithin7Days > 0
            ? round(($dispensedWithin7Days / $totalWithin7Days) * 100, 2)
            : 0;

            /*
            |--------------------------------------------------------------------------
            | Dispensed with indication
            |--------------------------------------------------------------------------
             */
            $withIndication = $wcbpDetails->filter(function ($item) {
                return $item->status === 'Dispensed'
                && !is_null($item->indication_id)
                && $item->indication_id != 0;
            })->count();

            Log::info('$withIndication');
            Log::info($withIndication);

            $withIndicationPercentage = $totalWcbp > 0
            ? round(($withIndication / $totalWcbpdispensed) * 100, 2)
            : 0;

            /*
            |--------------------------------------------------------------------------
            | Valid pregnancy test
            |--------------------------------------------------------------------------
             */
            $validPregnancyTest = $wcbpDetails->filter(function ($item) {

                if (
                    !$item->last_negative_preg_date ||
                    !$item->declaration_date ||
                    $item->status !== 'dispensed'
                ) {
                    return false;
                }

                $negDate = \Carbon\Carbon::parse($item->last_negative_preg_date)->startOfDay();
                $declDate = \Carbon\Carbon::parse($item->declaration_date)->startOfDay();

                return $negDate->between(
                    $declDate->copy()->subDays(3),
                    $declDate
                );

            })->count();

            $validPregnancyTestPercentage = $totalWcbpdispensed > 0
            ? round(($validPregnancyTest / $totalWcbpdispensed) * 100, 2)
            : 0;

            /*
            |--------------------------------------------------------------------------
            | Confirmed Counselling
            |--------------------------------------------------------------------------
             */
            $wcbpIds = $wcbpDetails->pluck('id')->filter()->unique()->values();

            $confirmedIds = PAFConfirmation::whereIn('paf_detail_id', $wcbpIds)
                ->where('is_confirmed', 1)
                ->pluck('paf_detail_id')
                ->unique();

            $confirmedWcbp = $wcbpIds->intersect($confirmedIds)->count();

            $confirmedWcbpPercentage = $totalWcbp > 0
            ? round(($confirmedWcbp / $totalWcbp) * 100, 2)
            : 0;

            /*
            |--------------------------------------------------------------------------
            | Valid 4 week supply
            |--------------------------------------------------------------------------
             */
            $valid4weeksIds = PAFNonConformance::whereIn('paf_details_id', $wcbpIds)
                ->where('type', '!=', 'WCBP_MAX_4_WEEKS_SUPPLY')
                ->pluck('paf_details_id')
                ->unique();

            $valid4weeksWcbp = $valid4weeksIds->count();

            $valid4weeksWcbpPercentage = $totalWcbp > 0
            ? round(($valid4weeksWcbp / $totalWcbp) * 100, 2)
            : 0;

            /*
            |--------------------------------------------------------------------------
            | Response
            |--------------------------------------------------------------------------
             */
            return response()->json([
                'status' => 'S',
                'message' => 'PAF stats fetched successfully',
                'counts' => [
                    [
                        'label' => 'Total PAFs',
                        'count' => $totalWcbp,
                        'totalcount' => $totalWcbp,
                        'percentage' => 100,
                    ],
                    // [
                    //     'label' => 'PAFs Created Within 7 Days',
                    //     'count' => $totalWithin7Days,
                    //     'totalcount' => $totalPafs,
                    //     'percentage' => $totalPafs > 0
                    //     ? round(($totalWithin7Days / $totalPafs) * 100, 2)
                    //     : 0,
                    // ],
                    [
                        'label' => 'PAFs Dispensed ≤7 Days',
                        'count' => $dispensedWithin7Days,
                        'totalcount' => $totalWithin7Days,
                        'percentage' => $dispensedWithin7DaysPercentage,
                    ],
                    [
                        'label' => 'Dispensed with indication',
                        'count' => $withIndication,
                        'totalcount' => $totalWcbpdispensed,
                        'percentage' => $withIndicationPercentage,
                    ],
                    [
                        'label' => 'Valid pregnancy test',
                        'count' => $validPregnancyTest,
                        'totalcount' => $totalWcbpdispensed,
                        'percentage' => $validPregnancyTestPercentage,
                    ],
                    [
                        'label' => 'Confirmed Counselling',
                        'count' => $confirmedWcbp,
                        'totalcount' => $totalWcbp,
                        'percentage' => $confirmedWcbpPercentage,
                    ],
                    [
                        'label' => 'PAFS with Valid 4-Week Supply',
                        'count' => $valid4weeksWcbp,
                        'totalcount' => $totalWcbp,
                        'percentage' => $valid4weeksWcbpPercentage,
                    ],
                ],
            ]);

        } catch (\Exception $e) {

            Log::error('PAF Stats Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch stats',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to mark non-conformance.
     *
     * @author: Stalvin M
     *
     * @created-on: 20 April, 2026
     *
     * @updated-on: N/A
     */

    public function markNonConformance(Request $request)
    {
        DB::beginTransaction();

        try {
            $userId = auth()->id();

            $request->validate([
                'paf_details_id' => 'required|exists:paf_details,id',
                'note' => 'required|string|max:1000',
            ]);

            // Get PAF
            $paf = PafDetails::findOrFail($request->paf_details_id);

            // -----------------------------------------
            // Create Non-Conformance
            // -----------------------------------------
            $nonConf = PAFNonConformance::create([
                'paf_details_id' => $paf->id,
                'note' => $request->note,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            // -----------------------------------------
            // If WCBP → Update risk level
            // -----------------------------------------
            if ($paf->patient_category === 'WCBP') {

                PafDetails::where(function ($q) use ($paf) {
                    $q->where('id', $paf->id)
                        ->orWhere('parent_id', $paf->id);
                })->update([
                    'risk_level' => 'High Risk',
                ]);
            }

            DB::commit();

            try {

                $pafDetails = PafDetails::with(['drug', 'header'])->find($paf->id);

                // OLD VALUES
                $oldValues = [
                    'paf_no' => $pafDetails->paf_no,
                    'patient_id' => $pafDetails->header->patient_no ?? null,
                    'risk_level' => $paf->risk_level,
                    'note' => null,
                ];

                // NEW VALUES
                $newValues = [
                    'paf_no' => $pafDetails->paf_no,
                    'patient_id' => $pafDetails->header->patient_no ?? null,
                    'risk_level' => $paf->patient_category === 'WCBP' ? 'High Risk' : $paf->risk_level,
                    'note' => $request->note,
                    'non_conf_id' => $nonConf->id,
                ];

                CustomFunctions::audit(
                    module: 'PAF',
                    action: 'NON-CONFORMANCE ADDED',
                    referenceId: $nonConf->id,
                    referenceTable: 'paf_non_conformances',
                    oldValues: $oldValues,
                    newValues: $newValues,
                    changedFields: ['note', 'risk_level'],
                    description:
                    'Non-Conformance Added: A compliance issue has been recorded for PAF No ' . $pafDetails->paf_no . '. ' .
                    'Patient ID: ' . ($pafDetails->header->patient_no ?? 'N/A') . ', ' .
                    'Drug: ' . ($pafDetails->drug->drug_name ?? 'N/A') . '. ' .
                    'Note: "' . $request->note . '". ' .
                    (
                        $paf->patient_category === 'WCBP'
                        ? 'Risk Level updated to High Risk due to WCBP category.'
                        : 'Risk Level unchanged.'
                    )
                );

            } catch (\Exception $auditEx) {
                Log::error('Non-Conformance Audit Failed: ' . $auditEx->getMessage());
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Non-conformance marked successfully',
                'data' => $nonConf,
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to mark non-conformance',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * @function: to get confirmation text.
     *
     * @author: Stalvin M
     *
     * @created-on: 22 April, 2026
     *
     * @updated-on: N/A
     */
    public function getConfirmationText(Request $request)
    {
        try {
            $request->validate([
                'drug_id' => 'required|integer',
                'patient_category' => 'required|string',
                'type' => 'nullable|string',
            ]);

            $query = PAFConfirmationText::where('drug_id', $request->drug_id)
                ->where('patient_category', $request->patient_category)
                ->where('status', 1);

            if (!empty($request->type)) {
                $query->where('type', $request->type);
            }

            $record = $query->first(['id', 'type', 'note', 'drug_id', 'patient_category']);

            if (!$record) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'No confirmation text found',
                    'confirmation' => null,
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Confirmation text fetched successfully',
                'confirmation' => $record,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function revalidateNonConformance(Request $request)
    {
        try {

            $data = $request->all();

            $paf = PafDetails::findOrFail($data['paf_details_id']);

            $violations = [];

            // -----------------------------------------
            // DYNAMIC RULE TYPES (ONLY THESE WILL CHANGE)
            // -----------------------------------------
            $dynamicTypes = [
                'WCBP_NEG_PREG_INVALID_RANGE',
                'WCBP_MAX_1_CYCLE',
                'THALIDOMIDE_DOSAGE_RULE',
                'RISK_NOT_CONFIRMED',
            ];

            // -----------------------------------------
            // 1. FETCH EXISTING FROM DB
            // -----------------------------------------
            $existing = PAFNonConformance::where('paf_details_id', $paf->id)->get();

            $existingMap = []; // store by type

            foreach ($existing as $row) {

                // detect rule type from note
                $rule = NonConformanceRules::where('description', $row->note)->first();

                $type = $rule->conformance_type ?? 'UNKNOWN';

                $existingMap[$type] = [
                    'id' => $row->id,
                    'type' => $type,
                    'note' => $row->note,
                    'existing' => true,
                    'created_at' => $row->created_at,
                ];
            }

            // -----------------------------------------
            // SAFE INPUTS
            // -----------------------------------------
            $cycles = $data['cycles'] ?? null;
            $totalSupply = $data['total_supply'] ?? null;
            $drugCycles = $data['drug_cycles'] ?? [];
            $negPregDate = $data['last_negative_preg_date'] ?? null;

            $drugId = $data['drug_id'] ?? $paf->drug_id;
            $patientCat = $data['patient_category'] ?? $paf->patient_category;

            // -----------------------------------------
            // RULE MASTER
            // -----------------------------------------
            $rules = NonConformanceRules::where('status', 1)
                ->get()
                ->keyBy('conformance_type');

            // -----------------------------------------
            // RULE CONDITIONS
            // -----------------------------------------
            $ruleResults = [];

            // ---- RULE 1 ----
            $ruleResults['WCBP_NEG_PREG_INVALID_RANGE'] = (function () use ($patientCat, $negPregDate, $paf) {

                if ($patientCat !== 'WCBP') {
                    return false;
                }

                if (!$negPregDate) {
                    return true;
                }

                $negDate = Carbon::parse($negPregDate)->startOfDay();

                $referenceDate = !empty($paf->declaration_date)
                ? Carbon::parse($paf->declaration_date)->startOfDay()
                : Carbon::today();

                $threeDaysAgo = $referenceDate->copy()->subDays(3);

                return $negDate->lt($threeDaysAgo) || $negDate->gt($referenceDate);
            })();

            // ---- RULE 2 ----
            $ruleResults['WCBP_MAX_1_CYCLE'] = (function () use ($patientCat, $cycles) {

                if ($patientCat !== 'WCBP') {
                    return false;
                }

                preg_match('/\d+/', $cycles ?? '', $matches);
                $cycleCount = isset($matches[0]) ? (int) $matches[0] : 0;

                return $cycleCount > 1;
            })();

            // ---- RULE 3 ----
            $ruleResults['THALIDOMIDE_DOSAGE_RULE'] = (function () use ($patientCat, $drugId, $totalSupply, $drugCycles) {

                if (!in_array($patientCat, ['WCBP', 'WNCBP'])) {
                    return false;
                }

                $drugName = CustomFunctions::getDrugName($drugId);

                if (!in_array($drugName, ['50mg - Thalidomide', '100mg - Thalidomide Tablet'])) {
                    return false;
                }

                preg_match('/\d+/', $totalSupply ?? '', $weekMatches);
                $weeks = isset($weekMatches[0]) ? (int) $weekMatches[0] : 0;

                if ($weeks <= 0) {
                    return false;
                }

                $expectedCapsules = $weeks * 7;

                foreach ($drugCycles as $dose) {

                    preg_match('/\d+/', $dose['cap_per_cycle'] ?? '', $capsuleMatches);
                    $capsules = isset($capsuleMatches[0]) ? (int) $capsuleMatches[0] : 0;

                    if ($capsules != $expectedCapsules) {
                        return true;
                    }
                }

                return false;
            })();

            // ---- RULE 3 ----
            $ruleResults['RISK_NOT_CONFIRMED'] = (function () use ($request) {

                // Only validate if field is sent
                if (!$request->has('risk_confirmed')) {
                    return false;
                }

                return (int) $request->risk_confirmed === 0;
            })();

            // -----------------------------------------
            // 2. BUILD FINAL VIOLATIONS LIST
            // -----------------------------------------
            foreach ($rules as $type => $rule) {

                $isDynamic = in_array($type, $dynamicTypes);

                if ($isDynamic) {

                    // APPLY LIVE VALIDATION
                    if (!empty($ruleResults[$type])) {

                        // show NEW violation
                        $violations[] = [
                            'id' => $rule->id,
                            'type' => $type,
                            'note' => $rule->description,
                            'existing' => false,
                        ];
                    }

                    // if false → DO NOT include (removes old one)

                } else {

                    // NON-DYNAMIC → always keep if exists
                    if (isset($existingMap[$type])) {
                        $violations[] = $existingMap[$type];
                    }
                }
            }

            return response()->json([
                'status' => 'S',
                'violations' => array_values($violations),
            ]);

        } catch (\Exception $e) {

            \Log::error('Revalidation failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'E',
                'message' => 'Validation failed',
            ]);
        }
    }
    public function checkOffLabelReasons(Request $request)
    {
        try {

            $data = $request->all();

            $reasons = [];

            // -----------------------------------------
            // SAFE INPUTS
            // -----------------------------------------
            $dob = $data['dob'] ?? null;
            $isOtherIndc = $data['is_other_indc'] ?? 0;
            $patientCat = $data['patient_category'] ?? null;

            // -----------------------------------------
            // RULE 1 → Under 18
            // -----------------------------------------
            if ($dob) {

                $age = Carbon::parse($dob)->age;

                if ($age < 18) {
                    $reasons[] = [
                        'type' => 'UNDER_18_OFF_LABEL',
                        'message' => 'The patient is under 18 years of age.',
                    ];
                }
            }

            // -----------------------------------------
            // RULE 2 → Other Indication
            // -----------------------------------------
            if ($isOtherIndc == 1) {

                $reasons[] = [
                    'type' => 'OTHER_INDICATION_OFF_LABEL',
                    'message' => 'An indication outside the approved list (‘Other’) has been selected.',
                ];
            }

            // -----------------------------------------
            // FINAL RESPONSE
            // -----------------------------------------
            return response()->json([
                'status' => 'S',
                'off_label' => count($reasons) > 0,
                'reasons' => $reasons,
            ]);

        } catch (\Exception $e) {

            \Log::error('Off-label check failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'status' => 'E',
                'message' => 'Off-label validation failed',
            ]);
        }
    }

    /**
     * @function: Get All Paf's both single and bluk.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05 May, 2026
     *
     * @updated-on: N/A
     */

    public function getAllPafReport(Request $request)
    {
        try {

            /*
            FIRST GET ALL PAF DETAILS
            Parent = paf_details rows
             */
            $details = PafDetails::latestVersion()
                ->with([
                    'header:id,patient_no,gender,paf_status,slug,created_at',

                    'indication:id,name',

                    'drug:id,drug_name,drug_form',

                    'institutions:id,name',

                    'prescriber' => function ($q) {
                        $q->select('id', 'name', 'lastname')
                            ->with('prescriber_data');
                    },

                    'drugCycles' => function ($q) {
                        $q->latestVersion();
                    },
                ])
                ->orderBy('id', 'desc')
                ->get();

            $formatted = $details->map(function ($detail) {
                Log::info('$detail');
                Log::info($detail->status);
                return [

                    /* DETAIL = PARENT */
                    'detail_id' => $detail->id,
                    'header_id' => $detail->pafheader_id,
                    'paf_no' => $detail->paf_no,
                    'status' => $detail->status,
                    'declaration_date' => $detail->declaration_date,
                    'declaration_name' => $detail->declaration_name,
                    'patient_initials' => $detail->patient_initials,
                    'dob' => $detail->patient_dob,
                    'dob_display' => $detail->patient_dob

                    ? \Carbon\Carbon::parse($detail->patient_dob)->format('d/M/Y')
                    : null,
                    'is_reviewed' => $detail->is_reviewed,
                    'patient_category' => $detail->patient_category ?? null,
                    'is_retrospective' => $detail->is_retrospective,
                    'indication' => $detail->indication->name ?? null,
                    'drug_name' => $detail->drug->drug_name ?? null,
                    'non_conformance' => $detail->non_conformance,
                    'prescriber' => $detail->prescriber
                    ? $detail->prescriber->name . ' ' . $detail->prescriber->lastname
                    : null,

                    'institution_data' => $detail->institutions
                    ? [
                        'id' => $detail->institutions->id,
                        'name' => $detail->institutions->name,
                    ]
                    : null,

                    /* HEADER INFO */
                    'paf_id' => $detail->header->paf_no ?? null,
                    'patient_id' => $detail->header->patient_no ?? null,
                    'gender' => $detail->header->gender ?? null,
                    'paf_status' => $detail->header->paf_status ?? null,
                    'slug' => $detail->header->slug ?? null,
                    'created_at' => $detail->header->created_at ?? null,
                ];
            });

            return response()->json([
                'status' => 'S',
                'message' => 'PAF report fetched successfully',
                'data' => $formatted,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => 'Failed to fetch report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
