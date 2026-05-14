<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
use App\Models\EmailTemplate;
use App\Models\PharmacistDetails;
use App\Models\PharmacistMedication;
use App\Models\PrescriberDetails;
use App\Models\PrescriberMedication;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;
use Mail;

class ApprovalApiController extends Controller
{
    /**
     * @function: to fetch Reg list.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 17 Feb, 2026
     *
     * @updated-on: N/A
     */

    public function index(Request $request)
    {
        try {
            // Fetch users with roles
            // $reg_list = User::with('role:id,rolename')
            //     ->whereIn('role_id', [2, 3])
            //     ->orderBy('updated_at', 'desc')
            //     ->get();
            $reg_list = User::with('role:id,rolename')
                ->whereHas('role', function ($query) {
                    $query->whereIn('rolename', ['Prescriber', 'Pharmacist']);
                })
                ->orderBy('updated_at', 'desc')
                ->get();
            // Load role-based relations
            foreach ($reg_list as $user) {
                if ($user->role && $user->role->rolename === 'Prescriber') {
                    $user->load([
                        'prescriberDetails',
                    ]);
                }

                if ($user->role && $user->role->rolename === 'Pharmacist') {
                    $user->load([
                        'pharmacistDetails',
                    ]);
                }
                // Add extra variable for pharmacist role
                $user->pharmacist_role = $user->pharmacistDetails->first()?->role ?? null;
            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'reg_list' => $reg_list,
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
     * @function: to Reg Details by Slug.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 17 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function fetchRegDetailsBySlug($slug)
    {
        try {
            // Fetch user with role
            $reg_deatils = User::with('role:id,rolename')
                ->where('slug', $slug)
                ->first();

            if (!$reg_deatils) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            // Load role-based relations
            if ($reg_deatils->role && $reg_deatils->role->rolename === 'Prescriber') {
                $reg_deatils->load('prescriberDetails');
            }

            if ($reg_deatils->role && $reg_deatils->role->rolename === 'Pharmacist') {
                $reg_deatils->load('pharmacistDetails');
            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'reg_deatils' => $reg_deatils,
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
     * @function: to Update Reg Status details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 16 Feb, 2026
     *
     * @updated-on: N/A
     */

    public function updateRegStatus(request $request)
    {
        try {
            DB::beginTransaction();
            // Validate request
            $yesterday = Carbon::today()->subDay()->toDateString();
            $request->validate([
                'slug' => 'required',
                'reg_status' => 'required|in:Approved,Rejected',
                'reject_reason' => 'nullable|required_if:reg_status,Rejected|string',
                'status' => 'required|in:1,0',
            ]);
            // ================= FETCH USER =================
            $user = User::where('id', $request->user_id)->first();
            if ($user->rolename === 'Pharmacist') {
                // Fetch pharmacist deatils
                $userDetails = PharmacistDetails::where('user_id', $request->user_id)
                    ->where('institution_id', $request->institution_id)
                    ->first();
                // Fetch pharmacist deatils
                $pharmacist_id = $userDetails->id;

            }
            // Fetch prescriber deatils
            if ($user->rolename === 'Prescriber') {
                $userDetails = PrescriberDetails::where('user_id', $request->user_id)
                    ->where('institution_id', $request->institution_id)
                    ->first();
                $prescriber_id = $userDetails->id;

            }
            // Fetch prescriber deatils
            if (!$userDetails) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Registration not found',
                ]);
            }

            $userDetails->update([
                'reg_status' => $request->reg_status,
                'rejection_reason' => $request->reg_status === 'Rejected'
                ? $request->reject_reason
                : null,
            ]);
            // ================= CAPTURE OLD DATA =================
            $oldUserData = $user->toArray();

            $oldRoleData = [];

            if ($request->rolename === 'Pharmacist') {
                $roleData = PharmacistDetails::where('user_id', $user->id)->first();
                $oldRoleData = $roleData ? $roleData->toArray() : [];
            }

            if ($request->rolename === 'Prescriber') {
                $roleData = PrescriberDetails::where('user_id', $user->id)->first();
                $oldRoleData = $roleData ? $roleData->toArray() : [];
            }

            // ================= PREPARE UPDATE =================
            // $updateData = [
            //     'reg_status' => $request->reg_status,
            //     'status' => $request->status,
            // ];

            if ($request->reg_status === 'Rejected') {
                // Expire medications
                if ($request->rolename === 'Pharmacist') {
                    PharmacistMedication::where('user_id', $request->user_id)
                        ->update([
                            'expired' => 1,
                            'expiry_reason' => 'User In-activated',
                            'updated_at' => now(),
                            'updated_by' => Auth::id(),
                        ]);
                }
                if ($request->rolename === 'Prescriber') {
                    PrescriberMedication::where('user_id', $request->user_id)
                        ->update([
                            'expired' => 1,
                            'expiry_reason' => 'User In-activated',
                            'updated_at' => now(),
                            'updated_by' => Auth::id(),
                        ]);
                }

                $updateData['rejection_reason'] = $request->reject_reason;
                $emailTemplate = EmailTemplate::where('template_name', 'Account Rejected')->first();
                if (isset($emailTemplate)) {
                    $actionText = null;
                    $actionUrl = null;
                    $userdata = [
                        'firstname' => $request->name,
                        'email' => $request->email,
                        'rejection_reason' => $request->reject_reason,
                    ];
                    $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                    $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                    Mail::to($request->email)->queue(
                        new RegistrationRejectionMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl)
                    );
                }
            } else {
                $updateData['rejection_reason'] = null;
                User::where('id', $request->user_id)->update(['status' => 1, 'reg_status' => 'Approved']);
                $emailTemplate = EmailTemplate::where('template_name', 'Account Approved')->first();
                if (isset($emailTemplate)) {
                    $actionText = null;
                    $actionUrl = null;
                    $app_url = config('values.APP_URL');
                    $userdata = [
                        'firstname' => $request->name,
                        'email' => $request->email,
                        'app_url' => $app_url,
                    ];
                    $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                    $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                    Mail::to($request->email)->queue(
                        new RegistrationRejectionMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl)
                    );
                }
            }
            // ================= UPDATE USER =================
            $user->update($updateData);

            // ================= FETCH NEW DATA =================
            $newUserData = $user->fresh()->toArray();

            $newRoleData = [];

            if ($request->rolename === 'Pharmacist') {
                $roleData = PharmacistDetails::where('user_id', $user->id)->first();
                $newRoleData = $roleData ? $roleData->toArray() : [];
            }

            if ($request->rolename === 'Prescriber') {
                $roleData = PrescriberDetails::where('user_id', $user->id)->first();
                $newRoleData = $roleData ? $roleData->toArray() : [];
            }

            // Remove sensitive fields
            unset($oldUserData['password']);
            unset($newUserData['password']);

            // ================= DYNAMIC MODULE NAME =================
            $moduleName = $request->rolename === 'Pharmacist'
            ? 'Pharmacist Registration'
            : 'Prescriber Registration';

            // ================= COMBINED AUDIT =================
            CustomFunctions::audit(
                module: $moduleName,
                action: 'UPDATE',
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: [
                    'user' => $oldUserData,
                    strtolower($request->rolename) => $oldRoleData,
                ],
                newValues: [
                    'user' => $newUserData,
                    strtolower($request->rolename) => $newRoleData,
                ],
                changedFields: array_keys($user->getChanges()),
                description: $request->reg_status === 'Approved'
                ? 'User registration approved'
                : 'User registration rejected'
            );

            DB::commit();
            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.saved_success'),
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Update Reg Status Error', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to fetch Reg list.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 17 Feb, 2026
     *
     * @updated-on: N/A
     */

    public function fetchAllPharmacist(Request $request)
    {
        try {
            // Fetch all pharmacist with roles
            $pharmacist_list = User::select(
                'id',
                'name',
                'lastname',
                'email',
                'created_by',
                'updated_by',
                'created_at',
                'updated_at'
            )
                ->whereIn('role_id', [3])
                ->orderBy('updated_at', 'desc')
                ->get();

            // Load role-based relations
            // foreach ($pharmacist_list as $user) {

            //     if ($user->role && $user->role->rolename === 'Pharmacist') {
            //         $user->load([
            //             'pharmacistDetails',
            //         ]);
            //     }
            //     // Add extra variable for pharmacist role
            //     $user->pharmacist_role = $user->pharmacistDetails->first()?->role ?? null;
            // }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'pharmacist_list' => $pharmacist_list,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }
}
