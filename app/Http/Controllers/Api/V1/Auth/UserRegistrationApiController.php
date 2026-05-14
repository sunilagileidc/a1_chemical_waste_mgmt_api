<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\Drugs;
use App\Models\InstitutionContacts;
use App\Models\Institutions;
use App\Models\PharmacistDetails;
use App\Models\PharmacistMedication;
use App\Models\PharmacistWholesaler;
use App\Models\PrescriberDetails;
use App\Models\PrescriberMedication;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;

class UserRegistrationApiController extends Controller
{
    /**
     * @function: to validate Registration Otp.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function validateRegistrationOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email', 'max:255'],
            'otp' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }
        try {
            $otp = \Hash::make((int) $request->otp);
            $user = User::where('email', $request->email)
                ->first();
            $currenttime = date('Y-m-d h:i:s');
            if ($user) {

                if ($currenttime > $user->otp_valid_until) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
                }
                if (!Hash::check($request->otp, $user->otp)) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
                } else {
                    $user->otp = null;
                    $user->status = 1;
                    $user->is_otp_validated = 1;
                    $user->otp_valid_until = null;
                    $user->save();

                    $userid = $user->id;
                    $logtype = 'Registration';
                    $title = 'Registration Successful';
                    $description = $user->name . ' ' . $user->lastname . ' Registered Successfully';
                    $createdby = $user->id;
                    $creationdate = date('Y-m-d');

                }
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.registration_success')]);
            } else {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
            }
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to register prescriber.
     *
     * @author: Stalvin M
     *
     * @created-on: 17 Feb, 2026
     *
     * @updated-on: 08 Apr, 2026
     */

    public function prescriberRegister(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'reg_no' => 'required',
            'job_title' => 'required',
            'institution_id' => 'required',
            'address' => 'required',
            'medications' => 'required',
            'email' => 'required',
            'password' => 'required',
            'signature' => 'required',
            'signature_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E',
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            if ($request->from_page != 'my-profile') {
                $userExists = User::where('email', $request->email)->exists();

                if ($userExists) {
                    return response()->json([
                        'status' => 'E',
                        'message' => 'An account with this email already exists. Please log in and add the selected hospital from your My Profile section.',
                    ]);
                }
            }
            DB::beginTransaction();

            $roleid = CustomFunctions::getRoleIdByName('Prescriber');

            // ================= CREATE / FETCH USER =================
            if ($request->from_page != 'my-profile') {
                $user = User::create([
                    'name' => Str::ucfirst($request->name),
                    'lastname' => Str::ucfirst($request->lastname),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'address' => $request->address,
                    'signature' => $request->signature,
                    'signature_date' => $request->signature_date,
                    'role_id' => $roleid,
                    'mobile' => $request->phone_no ?? null,
                    'status' => 0,
                    'reg_status' => 'Awaiting Approval',
                ]);
            } else {
                $user = User::find($request->user_id);
            }

            // ================= PRESCRIBER DETAILS =================
            if ($request->from_page != 'my-profile') {

                $prescriberDetails = PrescriberDetails::create([
                    'user_id' => $user->id,
                    'reg_no' => $request->reg_no,
                    'job_title' => $request->job_title,
                    'institution_id' => $request->institution_id,
                    'reg_status' => 'Awaiting Approval',
                    'created_by' => $user->id,
                ]);

                $existing = null;
                $oldData = [];

            } else {
                Log::info('$request');
                // Log::info($request->id);
                Log::info($request);
                Log::info($request->institution_id);
                // die();
                $existing = PrescriberDetails::where('user_id', $user->id)
                    ->where('institution_id', $request->institution_id)
                    ->first();

                $oldData = $existing ? $existing->toArray() : [];

                $prescriberDetails = PrescriberDetails::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'institution_id' => $request->institution_id,
                    ],
                    [
                        'reg_no' => $request->reg_no,
                        'job_title' => $request->job_title,
                        'reg_status' => 'Awaiting Approval',
                        'rejection_reason' => null,
                        'created_by' => $user->id,
                    ]
                );

            }

            // ================= MEDICATIONS =================
            $today = Carbon::today();
            $createdMedications = [];

            foreach ($request->medications as $medication) {

                $drug = Drugs::select('validity')->where('id', $medication)->first();

                if (!$drug) {
                    continue;
                }

                $validityMonths = (int) $drug->validity;

                $startDate = $today;
                $endDate = $today->copy()->addMonths($validityMonths);

                $med = PrescriberMedication::create([
                    'prescriber_id' => $prescriberDetails->id,
                    'user_id' => $user->id,
                    'drug_id' => $medication,
                    'is_check' => 1,
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                    'version' => 1,
                    'expired' => 0,
                    'created_by' => $user->id,
                ]);

                $createdMedications[] = $med->toArray();
            }

            // ================= COMBINED AUDIT =================

            // Remove sensitive fields
            $userData = $user->toArray();
            unset($userData['password']);

            $prescriberData = $prescriberDetails->toArray();

            $newValues = [
                'user' => $userData,
                'prescriber' => $prescriberData,
                'medications' => $createdMedications ?? [],
            ];

            $oldValues = [];
            $action = 'CREATE';

            if ($request->from_page == 'my-profile') {

                $action = $existing ? 'UPDATE' : 'CREATE';

                $oldValues = [
                    'user' => $user->toArray(),
                    'prescriber' => $oldData ?? [],
                ];
            }

            // ================= DYNAMIC DESCRIPTION =================

            if ($request->from_page == 'my-profile') {

                $institutionName = Institutions::where('id', $request->institution_id)->value('name');

                if ($action == 'CREATE') {
                    $description = "Prescriber added institution ({$institutionName}) from profile";
                } else {
                    $description = "Prescriber institution ({$institutionName}) updated from profile";
                }

            } else {

                if ($action == 'CREATE') {
                    $description = 'Prescriber registration completed with user, institution, and medications';
                } else {
                    $description = 'Prescriber registration details updated';
                }
            }

            CustomFunctions::audit(
                module: 'Prescriber Registration',
                action: $action,
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: !empty($oldValues) ? $oldValues : null,
                newValues: $newValues,
                changedFields: $action == 'UPDATE'
                ? array_keys($prescriberDetails->getChanges())
                : null,
                description: $description
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Prescriber registration completed successfully',
            ]);

        } catch (\Exception $e) {

            Log::info($e);
            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to register prescriber.
     *
     * @author: Stalvin M
     *
     * @created-on: 18 Feb, 2026
     *
     * @updated-on: 08 Apr, 2026
     */

    public function pharmacistRegister(Request $request)
    {
        Log::info('$request');
        Log::info($request);
        // die();
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required',
            'password' => 'required',
            'reg_no' => 'required',
            'institution_type' => 'required',
            'institution_id' => 'required',
            'dispensing_address' => 'required',
            'medications' => 'required',
            'signature' => 'required',
            'signature_date' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E',
                'message' => $validator->errors()->first(),
            ]);
        }

        try {
            if ($request->from_page != 'my-profile') {
                $userExists = User::where('email', $request->email)->exists();

                if ($userExists) {
                    return response()->json([
                        'status' => 'E',
                        'message' => 'An account with this email already exists. Please log in and add the selected institution from your My Profile section.',
                    ]);
                }
            }
            DB::beginTransaction();

            $roleid = CustomFunctions::getRoleIdByName('Pharmacist');

            if (!$roleid) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Pharmacist role not found',
                ]);
            }

            // ================= USER =================
            if ($request->from_page != 'my-profile') {

                $user = User::create([
                    'name' => Str::ucfirst($request->name),
                    'lastname' => Str::ucfirst($request->lastname),
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'signature' => $request->signature,
                    'signature_date' => $request->signature_date,
                    'role_id' => $roleid,
                    'mobile' => $request->phone_no ?? null,
                    'status' => 0,
                    'reg_status' => 'Awaiting Approval',
                ]);
            } else {
                $user = User::find($request->user_id);
            }

            // ================= PHARMACIST DETAILS =================
            if ($request->from_page != 'my-profile') {
                $pharmacistDetails = PharmacistDetails::create([
                    'user_id' => $user->id,
                    'reg_no' => $request->reg_no,
                    'phone_no' => $request->phone_no ?? null,
                    'dispensing_address' => $request->dispensing_address,
                    'delivery_address' => $request->delivery_address,
                    'delivery_post_code' => $request->delivery_post_code,
                    'ordering_address' => $request->ordering_address,
                    'ordering_post_code' => $request->ordering_post_code,
                    'institution_type' => $request->institution_type,
                    'institution_id' => $request->institution_id,
                    'reg_status' => 'Awaiting Approval',
                    'role' => $request->role,
                    'created_by' => $request->user_id,
                ]);

                $existing = null;
                $oldData = [];

                // Lead Pharmacist → Institution Contact
                if ($request->role == 'Lead Pharmacist') {
                    InstitutionContacts::create([
                        'name' => $user->name . ' ' . $request->lastname,
                        'user_id' => $user->id,
                        'email' => $request->email,
                        'status' => 1,
                        'institution_id' => $request->institution_id,
                        'created_by' => $user->id,
                    ]);
                }

            } else {

                $existing = PharmacistDetails::where('user_id', $user->id)
                    ->where('institution_id', $request->institution_id)
                    ->first();

                $oldData = $existing ? $existing->toArray() : [];

                $pharmacistDetails = PharmacistDetails::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'institution_id' => $request->institution_id,
                    ],
                    [
                        'reg_status' => 'Awaiting Approval',
                        'rejection_reason' => null,
                        'reg_no' => $request->reg_no,
                        'phone_no' => $request->phone_no ?? null,
                        'dispensing_address' => $request->dispensing_address,
                        'delivery_address' => $request->delivery_address,
                        'delivery_post_code' => $request->delivery_post_code,
                        'ordering_address' => $request->ordering_address,
                        'ordering_post_code' => $request->ordering_post_code,
                        'institution_type' => $request->institution_type,
                        'role' => $request->role,
                        'created_by' => $request->user_id,
                    ]
                );
            }

            // ================= MEDICATIONS =================
            $today = Carbon::today();
            $medicationsData = [];

            foreach ($request->medications as $medication) {

                $drug = Drugs::select('validity')->where('id', $medication)->first();
                if (!$drug) {
                    continue;
                }

                $endDate = $today->copy()->addMonths((int) $drug->validity);

                $medicationsData[] = [
                    'pharmacist_id' => $pharmacistDetails->id,
                    'user_id' => $user->id,
                    'drug_id' => $medication,
                    'is_check' => 1,
                    'start_date' => $today,
                    'end_date' => $endDate,
                    'version' => 1,
                    'expired' => 0,
                    'created_by' => $user->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            PharmacistMedication::insert($medicationsData);

            // ================= WHOLESALERS =================
            $wholesalersData = [];

            if ($request->has('wholesaler_accounts') && is_array($request->wholesaler_accounts)) {
                foreach ($request->wholesaler_accounts as $wid => $acc) {
                    if (empty($acc)) {
                        continue;
                    }

                    $wholesalersData[] = [
                        'pharmacist_id' => $pharmacistDetails->id,
                        'wholesaler_id' => $wid,
                        'acc_no' => $acc,
                        'created_by' => $user->id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                if (!empty($wholesalersData)) {
                    PharmacistWholesaler::insert($wholesalersData);
                }
            }

            // ================= COMBINED AUDIT =================

            $userData = $user->toArray();
            unset($userData['password']);

            $newValues = [
                'user' => $userData,
                'pharmacist' => $pharmacistDetails->toArray(),
                'medications' => $medicationsData,
                'wholesalers' => $wholesalersData ?? [],
            ];

            $oldValues = [];
            $action = 'CREATE';

            if ($request->from_page == 'my-profile') {
                $action = $existing ? 'UPDATE' : 'CREATE';

                $oldValues = [
                    'user' => $user->toArray(),
                    'pharmacist' => $oldData ?? [],
                ];
            }

            CustomFunctions::audit(
                module: 'Pharmacist Registration',
                action: $action,
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: !empty($oldValues) ? $oldValues : null,
                newValues: $newValues,
                changedFields: $action == 'UPDATE'
                ? array_keys($pharmacistDetails->getChanges())
                : null,
                description: $action == 'CREATE'
                ? 'Pharmacist registration user details created'
                : 'Pharmacist registration updated from profile'
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Pharmacist registration completed successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();
            Log::error($e);

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function checkEmailExists(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);
        try {

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'E',
                    'message' => $validator->errors()->first(),
                ]);
            }

            $userExists = User::where('email', $request->email)->exists();

            if ($userExists) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'An account with this email already exists. Please log in and connect the institution from your profile.',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => 'Email is available.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

}
