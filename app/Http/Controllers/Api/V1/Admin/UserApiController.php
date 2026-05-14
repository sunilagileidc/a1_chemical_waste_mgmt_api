<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
use App\Models\Careers;
use App\Models\Category;
use App\Models\CustomerNewsletter;
use App\Models\EmailTemplate;
use App\Models\Events;
use App\Models\HeaderAnswer;
use App\Models\InstitutionContacts;
use App\Models\OtherUserDetails;
use App\Models\PharmacistDetails;
use App\Models\PharmacistMedication;
use App\Models\PharmacistWholesaler;
use App\Models\PrescriberDetails;
use App\Models\PrescriberMedication;
use App\Models\Products;
use App\Models\PromotionsOffers;
use App\Models\Role;
use App\Models\ServiceSlotBooking;
use App\Models\ServicesSlots;
use App\Models\Stores;
use App\Models\Testimonials;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Log;
use Mail;

class UserApiController extends Controller
{
    /**
     * @function: to fetch user details.
     *
     * @author: Rohith R
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchUser(Request $request)
    {
        try {
            $usersdata = User::with('role')
                ->whereHas('role', function ($query) {
                    $query->whereNotIn('rolename', ['Prescriber', 'Pharmacist', 'Nurse']);
                })
                ->orderBy('updated_at', 'desc')
                ->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to fetch user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 23 Mar, 2023
     *
     * @updated-on: N/A
     */
    public function fetchConnectedNurses(Request $request)
    {
        try {
            $userId = auth()->id();

            $usersdata = User::with('role', 'otherUserDetails.institution')
                ->whereHas('role', function ($query) {
                    $query->where('rolename', 'Nurse');
                })
                ->where('created_by', $userId)
                ->orderBy('updated_at', 'desc')
                ->get();

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'usersdata' => $usersdata,
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
     * @function: to update user 'is locked' status.
     *
     * @author: Santhosha G
     *
     * @created-on: 27 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function unlockUser(request $request)
    {
        try {
            $User = User::where('id', $request->id)->first();
            if ($User->is_locked == 'Y') {
                $status = User::where('id', $request->id)->update(['is_locked' => 'N', 'password_count' => 0]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to save user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 17 Feb, 2026
     *
     * @updated-on: 17 Feb, 2026
     */
    public function saveUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required',
        ]);
        try {
            $currenttime = date('Y-m-d h:i:s');
            $userId = auth()->id();
            $targetUser = Str::ucfirst($request->name) . ' ' . Str::ucfirst($request->lastname);
            if ($validator->fails()) {
                return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
            } else {
                $userexists = User::where('email', $request->email)->where('id', '!=', $request->id)->first();
                if ($userexists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_already_exists')]);
                }
                if ($request->password == null) {
                    $generated_password = rand(100000, 999999);
                    $password = Hash::make($generated_password);
                } else {
                    $password = Hash::make($request->password);
                }

                if ($request->id > 0) {
                    DB::beginTransaction();
                    $oldUser = User::where('id', $request->id)->first();

                    $users = User::where('id', $request->id)
                        ->update([
                            'salutation' => $request->salutation,
                            'name' => Str::ucfirst($request->name),
                            'lastname' => Str::ucfirst($request->lastname),
                            'gender' => $request->gender,
                            'dob' => $request->dob,
                            'email' => $request->email,
                            'mobile' => $request->mobile,
                            'mobile_code' => $request->mobile_code,
                            'role_id' => $request->role_id,
                            'address' => $request->address,
                            'country' => $request->country,
                            'state' => $request->state,
                            'city' => $request->city,
                            'status' => $request->status,
                            'email_subscription' => $request->email_subscription,
                            'postcode' => $request->postcode,
                            'description' => $request->description,
                            'image_url' => $request->image_url,
                            'updated_at' => $currenttime,
                            'updated_by' => $userId,
                        ]);

                    $users = User::with('role')->where('id', $request->id)->first();
                    if (!empty($request->institution_ids)) {
                        $this->saveOtherUserDetails($users, $request, $oldUser);
                    } else {
                        // USER ONLY AUDIT
                        $description = "User '{$targetUser}' ({$users->email}) has been updated. " . "Role: " . ($users->role->name ?? 'N/A') . ". " . "Updated by User ID: {$userId}.";
                        CustomFunctions::audit(
                            module: 'User Management',
                            action: 'UPDATE',
                            referenceId: $users->id,
                            referenceTable: 'users',
                            oldValues: $oldUser->toArray(),
                            newValues: $users->toArray(),
                            description: $description
                        );
                    }
                    DB::commit();

                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'userdata' => $users]);
                } else {
                    DB::beginTransaction();
                    $users = User::create([
                        'salutation' => $request->salutation,
                        'name' => Str::ucfirst($request->name),
                        'lastname' => Str::ucfirst($request->lastname),
                        'email' => $request->email,
                        'gender' => $request->gender,
                        'dob' => $request->dob,
                        'password' => $password,
                        'mobile' => $request->mobile,
                        'mobile_code' => $request->mobile_code,
                        'role_id' => $request->role_id,
                        'address' => $request->address,
                        'country' => $request->country,
                        'state' => $request->state,
                        'city' => $request->city,
                        'postcode' => $request->postcode,
                        'description' => $request->description,
                        'status' => $request->status,
                        'email_subscription' => $request->email_subscription,
                        'image_url' => $request->image_url,
                        'created_at' => $currenttime,
                        'created_by' => $userId,
                        'updated_at' => $currenttime,
                    ]);
                    if (!empty($request->institution_ids)) {
                        $this->saveOtherUserDetails($users, $request);
                    } else {
                        $description = "New user '{$targetUser}' ({$users->email}) has been created. " . "Role: " . ($users->role->name ?? 'N/A') . ". " . "Created by User ID: {$userId}.";
                        // USER ONLY AUDIT
                        CustomFunctions::audit(
                            module: 'User Management',
                            action: 'CREATE',
                            referenceId: $users->id,
                            referenceTable: 'users',
                            newValues: $users->toArray(),
                            description: $description,
                        );
                    }
                    DB::commit();
                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'userdata' => $users]);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to save nurse user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 06 Apr, 2026
     *
     * @updated-on: 06 Apr, 2026
     */
    private function saveOtherUserDetails($users, $request, $oldUser = null)
    {
        $validator = Validator::make($request->all(), [
            'institution_ids' => 'required|array',
            'institution_ids.*' => 'exists:institutions,id',
            'reg_no' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        // OLD DATA
        $oldInstitutionData = OtherUserDetails::where('user_id', $users->id)->get()->toArray();

        // DELETE removed
        OtherUserDetails::where('user_id', $users->id)
            ->whereNotIn('institution_id', $request->institution_ids)
            ->delete();

        $newInstitutionData = [];

        foreach ($request->institution_ids as $institutionId) {
            $record = OtherUserDetails::updateOrCreate(
                [
                    'user_id' => $users->id,
                    'institution_id' => $institutionId,
                ],
                [
                    'reg_no' => $request->reg_no ?? null,
                    'job_title' => $request->job_title ?? null,
                    'updated_by' => auth()->id(),
                ]
            );

            $newInstitutionData[] = $record->toArray();
        }

        // AUDIT
        CustomFunctions::audit(
            module: 'Nurses',
            action: $oldUser ? 'UPDATE' : 'CREATE',
            referenceId: $users->id,
            referenceTable: 'users',
            oldValues: [
                'user' => $oldUser ? $oldUser->toArray() : null,
                'institutions' => $oldInstitutionData,
            ],
            newValues: [
                'user' => $users->fresh()->toArray(),
                'institutions' => $newInstitutionData,
            ],
            description: $oldUser
            ? 'Nurse and institution details updated'
            : 'Nurse and institution details created'
        );
    }

    /**
     * @function: to update user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function updateUserStatus(request $request)
    {

        try {
            DB::beginTransaction();
            $yesterday = Carbon::today()->subDay()->toDateString();
            $emailTemplate = EmailTemplate::where('template_name', 'Account Status Update')->first();
            $user = User::where('id', $request->id)->first();
            $return_message = trans('returnmessage.updatedsuccessfully');

            // ================= CAPTURE OLD DATA =================
            $oldUserData = $user->toArray();
            unset($oldUserData['password']);

            // get role name safely
            $roleName = $user->role->rolename ?? null;

            $oldRoleData = [];

            if ($roleName === 'Pharmacist') {
                $roleData = PharmacistDetails::where('user_id', $user->id)->first();
                $oldRoleData = $roleData ? $roleData->toArray() : [];
            }

            if ($roleName === 'Prescriber') {
                $roleData = PrescriberDetails::where('user_id', $user->id)->first();
                $oldRoleData = $roleData ? $roleData->toArray() : [];
            }

            if ($user->status == 1) {
                $account_status = 'De-Activated';
                $status = User::where('id', $request->id)->update(['status' => 0]);
                if (isset($emailTemplate)) {
                    if (
                        $emailTemplate->is_mandatory === 1 ||
                        ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                    ) {
                        $actionText = null;
                        $actionUrl = null;
                        $userdata = ['firstname' => $user->full_name, 'email' => $user->email, 'account_status' => $account_status];
                        $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                        $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                        $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                        $message = 'Hi ' . $userdata['firstname'] . '<br> Your account has been ' . $account_status;
                        Mail::to($user->email)->queue(new RegistrationRejectionMail($parsedSubject, $message, $paresedSignature, $actionText, $actionUrl));
                    } else {
                        $return_message = trans('returnmessage.updated_success_unsend_user_mail');
                    }
                    //getting the pharmacst and institution deatils of userid
                    $user = User::with('role:id,rolename')
                        ->where('id', $request->id)
                        ->first();

                    if (!$user) {
                        return response()->json([
                            'status' => 'E',
                            'message' => 'User not found',
                        ]);
                    }
                    $oldMedications = [];
                    if ($user->role->rolename === 'Pharmacist') {

                        $oldMedications = PharmacistMedication::where('user_id', $request->id)
                            ->where('expired', 0)
                            ->get()
                            ->toArray();
                        // Updating the End date of drug to expired if user is inactive START
                        PharmacistMedication::where('user_id', $request->id)
                            ->update([
                                'expired' => 1,
                                'expiry_reason' => 'User In-activated',
                                'updated_by' => Auth::id(),
                            ]);
                        // Updating the End date of drug to expired if user is inactive END
                        $user->load(['pharmacistDetails']);
                        foreach ($user->pharmacistDetails as $pharmacist) {
                            if (empty($pharmacist->institution_id)) {
                                continue;
                            }

                            // get all contacts for this institution
                            $contacts = InstitutionContacts::where('institution_id', $pharmacist->institution_id)->get();
                            foreach ($contacts as $contact) {

                                if (empty($contact->email)) {
                                    continue;
                                }

                                $actionText = null;
                                $actionUrl = null;

                                $institutiondata = [
                                    'firstname' => $contact->name,
                                    'email' => $contact->email,
                                    'account_status' => $account_status,
                                ];

                                $message = 'Hi ' . $institutiondata['firstname'] . '<br>' .
                                    $user['firstname'] . ' account has been ' . $account_status;

                                $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $institutiondata);
                                $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $institutiondata);
                                $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $institutiondata);
                                Mail::to($institutiondata['email'])->queue(new RegistrationRejectionMail($parsedSubject, $message, $paresedSignature, $actionText, $actionUrl));
                            }

                        }

                    }
                    if ($user->role->rolename === 'Prescriber') {
                        $oldMedications = PrescriberMedication::where('user_id', $request->id)
                            ->where('expired', 0)
                            ->get()
                            ->toArray();
                        $user->load(['prescriberDetails']);
                        // Updating the End date of drug to expired if user is inactive START
                        PrescriberMedication::where('user_id', $request->id)
                            ->update([
                                'expired' => 1,
                                'expiry_reason' => 'User In-activated',
                                'updated_by' => Auth::id(),
                            ]);

                        // Updating the End date of drug to expired if user is inactive END
                        //Commented Mail for prescriber
                        // foreach ($user->prescriberDetails as $prescriber) {
                        //     if (empty($prescriber->institution_data)) {
                        //         continue;
                        //     }

                        //     foreach ($prescriber->institution_data as $institution) {

                        //         if (empty($institution['contact_email'])) {
                        //             continue;
                        //         }
                        //         $actionText = null;
                        //         $actionUrl = null;
                        //         $institutiondata = ['firstname' => $institution->name, 'email' => $institution->contact_email, 'account_status' => $account_status];
                        //         // Log::info('institutiondata for prescriber');
                        //         // Log::info($institutiondata);
                        //         $message = 'Hi ' . $institutiondata['firstname'] . '<br>' . $userdata['firstname'] . ' account has been ' . $account_status;
                        //         $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $institutiondata);
                        //         $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $institutiondata);
                        //         $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $institutiondata);

                        //         Mail::to($institutiondata['email'])
                        //             ->send(new RegistrationStatusMail(
                        //                 $parsedSubject,
                        //                 $message,
                        //                 $paresedSignature,
                        //                 $actionText,
                        //                 $actionUrl
                        //             ));
                        //     }

                        // }
                    }
                    $newMedications = [];

                    if (!empty($oldMedications)) {
                        $newMedications = collect($oldMedications)->map(function ($item) {
                            $item['expired'] = 1;
                            $item['expiry_reason'] = 'User In-activated';
                            return $item;
                        })->toArray();
                    }

                }
            } else {
                $account_status = 'Activated';
                $status = User::where('id', $request->id)->update(['status' => 1, 'is_suspicious_actor' => 0]);

                if (
                    $emailTemplate->is_mandatory === 1 ||
                    ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                ) {
                    $actionText = null;
                    $actionUrl = null;
                    $userdata = ['firstname' => $user->full_name, 'email' => $user->email, 'account_status' => $account_status];
                    $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                    $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                    $message = 'Hi ' . $userdata['firstname'] . '<br> Your account has been ' . $account_status;
                    Mail::to($user->email)->queue(new RegistrationRejectionMail($parsedSubject, $message, $paresedSignature, $actionText, $actionUrl));
                } else {
                    $return_message = trans('returnmessage.updated_success_unsend_user_mail');
                }
                if ($user->role->rolename === 'Pharmacist') {

                    $user->load(['pharmacistDetails']);
                    foreach ($user->pharmacistDetails as $pharmacist) {

                        if (empty($pharmacist->institution_id)) {
                            continue;
                        }

                        // get contacts mapped with institution_id
                        $contacts = InstitutionContacts::where('institution_id', $pharmacist->institution_id)->get();

                        foreach ($contacts as $contact) {

                            if (empty($contact->email)) {
                                continue;
                            }

                            $actionText = null;
                            $actionUrl = null;

                            $institutiondata = [
                                'firstname' => $contact->name,
                                'email' => $contact->email,
                                'account_status' => $account_status,
                            ];

                            $message = 'Hi ' . $institutiondata['firstname'] . '<br>' .
                                $user['firstname'] . ' account has been ' . $account_status;

                            $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $institutiondata);
                            $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $institutiondata);
                            $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $institutiondata);
                            Mail::to($institutiondata['email'])->queue(new RegistrationRejectionMail($parsedSubject, $message, $paresedSignature, $actionText, $actionUrl));
                        }
                    }
                }
                if ($user->role->rolename === 'Prescriber') {

                    $user->load(['prescriberDetails']);
                    // Log::info('$user for prescriber');
                    // Log::info($user);

                    // foreach ($user->prescriberDetails as $prescriber) {
                    //     if (empty($prescriber->institution_data)) {
                    //         continue;
                    //     }

                    //     foreach ($prescriber->institution_data as $institution) {

                    //         if (empty($institution['contact_email'])) {
                    //             continue;
                    //         }
                    //         $actionText = null;
                    //         $actionUrl = null;
                    //         $institutiondata = ['firstname' => $institution->name, 'email' => $institution->contact_email, 'account_status' => $account_status];
                    //         // Log::info('institutiondata for prescriber');
                    //         // Log::info($institutiondata);
                    //         $message = 'Hi ' . $institutiondata['firstname'] . '<br>' . $userdata['firstname'] . ' account has been ' . $account_status;
                    //         $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $institutiondata);
                    //         $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $institutiondata);
                    //         $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $institutiondata);

                    //         Mail::to($institutiondata['email'])
                    //             ->send(new RegistrationStatusMail(
                    //                 $parsedSubject,
                    //                 $message,
                    //                 $paresedSignature,
                    //                 $actionText,
                    //                 $actionUrl
                    //             ));
                    //     }

                    // }
                }
            }
            // ================= DYNAMIC MODULE NAME =================
            $moduleName = match ($roleName) {
                'Pharmacist' => 'Pharmacist Registration',
                'Prescriber' => 'Prescriber Registration',
                'Nurse' => 'Nurses',
                default => $roleName . ' Update',
            };
            // ================= FETCH UPDATED DATA =================
            if (!empty($oldMedications)) {

                $medicationTable = $roleName === 'Pharmacist'
                ? 'pharmacist_medications'
                : 'prescriber_medications';

                CustomFunctions::audit(
                    module: $moduleName,
                    action: 'UPDATE',
                    referenceId: $user->id,
                    referenceTable: $medicationTable,
                    oldValues: $oldMedications,
                    newValues: $newMedications,
                    description: 'All medications expired due to user ' . $account_status
                );
            }

            $user->refresh();

            $newUserData = $user->toArray();
            unset($newUserData['password']);

            $newRoleData = [];

            if ($roleName === 'Pharmacist') {
                $roleData = PharmacistDetails::where('user_id', $user->id)->first();
                $newRoleData = $roleData ? $roleData->toArray() : [];
            }

            if ($roleName === 'Prescriber') {
                $roleData = PrescriberDetails::where('user_id', $user->id)->first();
                $newRoleData = $roleData ? $roleData->toArray() : [];
            }

            // ================= AUDIT =================
            CustomFunctions::audit(
                module: $moduleName,
                action: 'UPDATE',
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: [
                    'user' => $oldUserData,
                    strtolower($roleName) => $oldRoleData,
                ],
                newValues: [
                    'user' => $newUserData,
                    strtolower($roleName) => $newRoleData,
                ],
                changedFields: array_keys($user->getChanges()),
                description: 'User status changed to ' . $account_status
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => $return_message]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function updateBlacklistStatus(request $request)
    {
        try {
            //zero for not blacklisted, one for blacklisted
            $user = User::where('id', $request->id)->first();
            if ($user->is_blacklisted == 0) {
                $status = User::where('id', $request->id)->update(['status' => 0, 'is_blacklisted' => 1, 'blacklist_comments' => $request->blacklist_reason]);
            } else {
                $status = User::where('id', $request->id)->update(['status' => 1, 'is_blacklisted' => 0, 'blacklist_comments' => $request->blacklist_reason]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch user details using Slug.
     *
     * @author: Santhosha G
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchUserBySlug($slug)
    {
        try {
            $user = User::with('role')->where('slug', $slug)->first();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch user details using Slug.
     *
     * @author: Santhosha G
     *
     * @created-on: 21 Mar, 2022
     *
     * @updated-on: N/A
     */
    public function fetchConnectedNursesBySlug($slug)
    {
        try {
            $user = User::with(['role', 'otherUserDetails'])->where('slug', $slug)->first();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to get User Data By Slug.
     *
     * @author: Santhosha G
     *
     * @created-on: 02 Apr, 2026
     *
     * @updated-on: N/A
     */
    public function fetchUserDataBySlug($slug)
    {
        try {

            // Fetch user with role first
            $user = User::with(['role:id,rolename', 'connectedNurses.otherUserDetails.institution', 'pharmacist'])
                ->where('slug', $slug)
                ->first();

            // Attach connected pharmacies & homecares
            $user->connected_pharmacies = $this->getConnectedInstitutions($user->id, 'Outpatient Pharmacy');
            $user->connected_homecares = $this->getConnectedInstitutions($user->id, 'Homecare');

            if (!$user) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            // 2 Load data based on role
            if ($user->role->rolename === 'Prescriber') {

                $user->load([
                    'prescriberDetails',
                    'prescriberMedications',
                ]);

            } elseif ($user->role->rolename === 'Pharmacist') {

                $user->load([
                    'pharmacistDetails',
                    'pharmacistMedications',
                ]);
                $pharmacist = $user->pharmacistDetails->first();

                Log::info('$pharmacist');
                Log::info($pharmacist);
                // Add extra variable for pharmacist role
                $user->pharmacist_role = $user->pharmacistDetails->first()?->role ?? null;
                //Commented this wholesaler as of now till we get clarification
                foreach ($user->pharmacistDetails as $pharmacist) {

                    $pharmacist->wholesalers = PharmacistWholesaler::where('pharmacist_id', $pharmacist->id)
                        ->with('wholesaler') // relation
                        ->get()
                        ->map(function ($item) {
                            return [
                                'wholesaler_id' => $item->wholesaler_id,
                                'wholesaler_name' => $item->wholesaler->name ?? null,
                            ];
                        });
                }

            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'user' => $user,
            ]);

        } catch (\Exception $e) {
            Log::info($e);

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to get Connected Institutions.
     *
     * @author: Santhosha G
     *
     * @created-on: 02 Apr, 2026
     *
     * @updated-on: N/A
     */
    private function getConnectedInstitutions($userId, $type)
    {
        return \App\Models\ConnectedPharmacies::with('pharmacy')
            ->where('user_id', $userId)
            ->whereHas('pharmacy', function ($q) use ($type) {
                $q->where('institution_type', $type);
            })
            ->orderByDesc('updated_at')
            ->get()
            ->map(function ($item) {

                $pharmacy = $item->pharmacy->first(); // assuming belongsTo

                return [
                    'id' => $item->id,
                    'connected_pharmacy_id' => $item->connected_pharmacy_id,
                    'user_id' => $item->user_id,
                    'institution_id' => $item->institution_id,
                    'status' => $item->status,

                    'name' => $pharmacy->name ?? null,
                    'address' => $pharmacy->address ?? null,
                    'post_code' => $pharmacy->post_code ?? null,
                    'pharmacy_id' => $pharmacy->id ?? null,
                ];
            });
    }

    /**
     * @function: to generate specile password.
     *
     * @author: Santhosha G
     *
     * @created-on: 26 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function generateStrongPassword($length = 12)
    {
        $uppercase = Str::random(1);
        $lowercase = strtolower(Str::random(1));
        $number = rand(0, 9);
        $specialChars = '~!@#$%';
        $special = $specialChars[rand(0, strlen($specialChars) - 1)];

        $remainingLength = $length - 4;

        $randomString = Str::random($remainingLength);

        $password = $uppercase . $lowercase . $number . $special . $randomString;

        return str_shuffle($password);
    }

    /**
     * @function: to send credentials to mail.
     *
     * @author: Santhosha G
     *
     * @created-on: 26 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function sendCredentials(Request $request)
    {
        try {
            $generated_password = $this->generateStrongPassword(12);
            $password = Hash::make($generated_password);

            $user = User::where('email', $request->email)
                ->update([
                    'password' => $password,
                ]);

            $emailTemplate = EmailTemplate::where('template_name', 'Send Credentials')->first();
            if (isset($emailTemplate)) {
                if (
                    $emailTemplate->is_mandatory === 1 ||
                    ($emailTemplate->is_mandatory === 0 && $request->email_subscription == 1)
                ) {
                    $actionText = null;
                    $actionUrl = null;
                    $userdata = ['firstname' => $request->name, 'password' => $generated_password];
                    $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                    $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                    Mail::to($request->email)->queue(new RegistrationRejectionMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.credentials_sent')]);
                } else {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_unsubscription')]);
                }
            }
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_template_unavailable')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing')]);
        }
    }

    public function fetchTradieUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Tradie')->first();

            $usersdata = User::where('role_id', $tradie_role->id)->where('is_blacklisted', 0)->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchBlacklistTradieUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Tradie')->first();

            $usersdata = User::where('role_id', $tradie_role->id)->where('is_blacklisted', 1)->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchPrincipalUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Principal')->first();

            $usersdata = User::where('role_id', $tradie_role->id)->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchTradieAllUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Tradie')->first();

            $different_user_id = HeaderAnswer::distinct('user_id')->where('status', '!=', 'Draft')
                ->where('status', '!=', null)->where('status', '!=', 'Pending')->pluck('user_id');

            $usersdata = User::with('headerApproved')->whereIn('id', $different_user_id)
                ->where('role_id', $tradie_role->id)
                ->orderBy('id', 'desc')
                ->get();

            // Log::info($usersdata);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchDashboardSuperUser($user_id)
    {
        $role_id = User::where('id', $user_id)->value('role_id');
        $rolename = Role::where('id', $role_id)->value('rolename');
        try {
            $count_dashboard = [];
            $user_array = [
                'name' => 'Users',
                'icon' => 'mdi mdi-account-multiple',
                'color' => 'success',
                'status' => [
                    [
                        'count' => User::where('status', 1)->count() - 1,
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => User::where('status', 0)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $events_array = [
                'name' => 'Events',
                'icon' => 'mdi mdi-calendar-check',
                'color' => 'warning',
                'status' => [
                    [
                        'count' => Events::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Events::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Events::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $promotion_array = [
                'name' => 'Promotions',
                'icon' => 'mdi mdi-ticket-percent',
                'color' => 'primary',
                'status' => [
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_array = [
                'name' => 'Products',
                'icon' => 'mdi mdi-view-module',
                'color' => 'error',
                'status' => [
                    [
                        'count' => Products::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Products::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Products::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $category_array = [
                'name' => 'Categories',
                'icon' => 'mdi mdi-apps',
                'color' => 'secondary',
                'status' => [
                    [
                        'count' => Category::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Category::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Category::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $career_array = [
                'name' => 'Careers',
                'icon' => 'mdi mdi-briefcase',
                'color' => '#b3d4fc',
                'status' => [
                    [
                        'count' => Careers::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Careers::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Careers::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $stores_array = [
                'name' => 'Stores',
                'icon' => 'mdi mdi-store-clock',
                'color' => '#f7b924',
                'status' => [
                    [
                        'count' => Stores::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Stores::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Stores::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $testimonials_array = [
                'name' => 'Testimonials',
                'icon' => 'mdi mdi-message-text-fast-outline',
                'color' => 'success',
                'status' => [
                    [
                        'count' => Testimonials::where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => Testimonials::where('status', 0)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $total_slots = (ServicesSlots::sum('slots') - ServiceSlotBooking::sum('slots'));
            $service_bookings_array = [
                'name' => 'ServiceBookings',
                'icon' => 'mdi mdi-notebook-check',
                'color' => 'error',
                'status' => [
                    [
                        'count' => ServiceSlotBooking::sum('slots'),
                        'color' => 'success',
                        'status_name' => 'Claims',
                    ],
                    [
                        'count' => $total_slots,
                        'color' => 'warning',
                        'status_name' => 'Available',

                    ],
                ],
            ];
            $newsletter_subscriptions = [
                'name' => 'NewsletterSubscriptions',
                'icon' => 'mdi mdi-email-newsletter',
                'color' => 'error',
                'status' => [
                    [
                        'count' => CustomerNewsletter::count(),
                        'color' => 'success',
                        'status_name' => 'Subscriptions',
                    ],
                ],
            ];
            array_push($count_dashboard, $user_array, $events_array, $promotion_array, $products_array, $category_array, $career_array, $stores_array, $service_bookings_array, $newsletter_subscriptions);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'count_dashboard' => $count_dashboard]);
            // Log::info($count_dashboard);

            // // $user_active = $user->where('status', 1)->count();
            // // $user_inactive = $user->where('status', 0)->count();

            // // $count_dashboard = array(
            // //     0 => array(
            // //         'count' => $user_active,
            // //     ),
            // //     1 => array(
            // //         'count' => $user_inactive,
            // //     ),
            // // );

            // // Log::info('count d  ' . $count_dashboard);

            // die();

            //zero for not blacklisted, one for blacklisted
            // $activeuser = User::where('status', 1)->count();
            // $inactiveuser = User::where('status', 0)->count();
            // $irevents = Events::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apevents = Events::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $reevents = Events::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $irpromo = PromotionsOffers::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $appromo = PromotionsOffers::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $repromo = PromotionsOffers::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $irprod = Products::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $approd = Products::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $reprod = Products::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $ircat = Category::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apcat = Category::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $recat = Category::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $ircar = Careers::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apcar = Careers::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $recar = Careers::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $irstore = Stores::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apstore = Stores::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $re_store = Stores::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $aptesti = Testimonials::where('status', 1)->count();
            // $retesti = Testimonials::where('status', 0)->count();

            // return response()->json(['status' => 'S',
            //     'activeuser' => $activeuser,
            //     'inactiveuser' => $inactiveuser,
            //     'irevents' => $irevents,
            //     'apevents' => $apevents,
            //     'reevents' => $reevents,
            //     'irpromo' => $irpromo,
            //     'appromo' => $appromo,
            //     'repromo' => $repromo,
            //     'irprod' => $irprod,
            //     'approd' => $approd,
            //     'reprod' => $reprod,
            //     'ircat' => $ircat,
            //     'apcat' => $apcat,
            //     'recat' => $recat,
            //     'ircar' => $ircar,
            //     'apcar' => $apcar,
            //     'recar' => $recar,
            //     'irstore' => $irstore,
            //     'apstore' => $apstore,
            //     're_store' => $re_store,
            //     'aptesti' => $aptesti,
            //     'retesti' => $retesti,
            //     'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function fetchDashboardMallAdmin($user_id)
    {
        $role_id = User::where('id', $user_id)->value('role_id');
        $rolename = Role::where('id', $role_id)->value('rolename');
        $mall_id = User::where('id', $user_id)->value('store_id');
        $store_id = Stores::where('mall_name', $mall_id)->where('lang', 'en')->pluck('id');
        try {
            $count_dashboard = [];
            $user_array = [
                'name' => 'Users',
                'icon' => 'mdi mdi-account-multiple',
                'color' => 'success',
                'status' => [
                    [
                        'count' => User::where('status', 1)->whereIn('store_id', $store_id)->count(),
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => User::where('status', 0)->whereIn('store_id', $store_id)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $events_array = [
                'name' => 'Events',
                'icon' => 'mdi mdi-calendar-check',
                'color' => 'warning',
                'status' => [
                    [
                        'count' => Events::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Events::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Events::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $promotion_array = [
                'name' => 'Promotions',
                'icon' => 'mdi mdi-ticket-percent',
                'color' => 'primary',
                'status' => [
                    [
                        'count' => PromotionsOffers::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_array = [
                'name' => 'Products',
                'icon' => 'mdi mdi-view-module',
                'color' => 'error',
                'status' => [
                    [
                        'count' => Products::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Products::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Products::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $category_array = [
                'name' => 'Categories',
                'icon' => 'mdi mdi-apps',
                'color' => 'pink',
                'status' => [
                    [
                        'count' => Category::where('approval_status', 'In Review')->where('store_id', $mall_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Category::where('approval_status', 'Approved')->where('store_id', $mall_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Category::where('approval_status', 'Rejected')->where('store_id', $mall_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $career_array = [
                'name' => 'Careers',
                'icon' => 'mdi mdi-briefcase',
                'color' => 'purple',
                'status' => [
                    [
                        'count' => Careers::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Careers::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Careers::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $stores_array = [
                'name' => 'Stores',
                'icon' => 'mdi mdi-store-clock',
                'color' => 'lime',
                'status' => [
                    [
                        'count' => Stores::where('approval_status', 'In Review')->whereIn('id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Stores::where('approval_status', 'Approved')->whereIn('id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Stores::where('approval_status', 'Rejected')->whereIn('id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $testimonials_array = [
                'name' => 'Testimonials',
                'icon' => 'mdi mdi-message-text-fast-outline',
                'color' => 'success',
                'status' => [
                    [
                        'count' => Testimonials::where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => Testimonials::where('status', 0)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $total_slots = (ServicesSlots::sum('slots') - ServiceSlotBooking::sum('slots'));
            $service_bookings_array = [
                'name' => 'ServiceBookings',
                'icon' => 'mdi mdi-notebook-check',
                'color' => 'error',
                'status' => [
                    [
                        'count' => ServiceSlotBooking::sum('slots'),
                        'color' => 'success',
                        'status_name' => 'Claims',
                    ],
                    [
                        'count' => $total_slots,
                        'color' => 'warning',
                        'status_name' => 'Available',

                    ],
                ],
            ];
            $newsletter_subscriptions = [
                'name' => 'NewsletterSubscriptions',
                'icon' => 'mdi mdi-email-newsletter',
                'color' => 'error',
                'status' => [
                    [
                        'count' => CustomerNewsletter::count(),
                        'color' => 'success',
                        'status_name' => 'Subscriptions',
                    ],
                ],
            ];

            array_push($count_dashboard, $user_array, $events_array, $promotion_array, $products_array, $category_array, $career_array, $stores_array, $service_bookings_array, $newsletter_subscriptions);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'count_dashboard' => $count_dashboard]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function fetchDashboardStoreAdmin($user_id)
    {
        $role_id = User::where('id', $user_id)->value('role_id');
        $rolename = Role::where('id', $role_id)->value('rolename');
        $store_id = User::where('id', $user_id)->value('store_id');
        try {
            $count_dashboard = [];
            $events_array = [
                'name' => 'Events',
                'icon' => 'mdi mdi-calendar-check',
                'color' => 'warning',
                'status' => [
                    [
                        'count' => Events::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Events::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Events::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $promotion_array = [
                'name' => 'Promotions',
                'icon' => 'mdi mdi-ticket-percent',
                'color' => 'primary',
                'status' => [
                    [
                        'count' => PromotionsOffers::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_array = [
                'name' => 'Products',
                'icon' => 'mdi mdi-view-module',
                'color' => 'error',
                'status' => [
                    [
                        'count' => Products::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Products::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Products::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];

            $career_array = [
                'name' => 'Careers',
                'icon' => 'mdi mdi-briefcase',
                'color' => 'purple',
                'status' => [
                    [
                        'count' => Careers::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Careers::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Careers::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_id = Products::where('store_id', $store_id)->distinct('header_id')->pluck('header_id');
            $slots_id = ServicesSlots::whereIn('service_id', $products_id)->pluck('id');
            $total_slots = (ServicesSlots::whereIn('service_id', $products_id)->sum('slots') - ServiceSlotBooking::whereIn('slots_id', $slots_id)->sum('slots'));
            $service_bookings_array = [
                'name' => 'ServiceBookings',
                'icon' => 'mdi mdi-notebook-check',
                'color' => 'error',
                'status' => [
                    [
                        'count' => ServiceSlotBooking::whereIn('slots_id', $slots_id)->sum('slots'),
                        'color' => 'success',
                        'status_name' => 'Claims',
                    ],
                    [
                        'count' => $total_slots,
                        'color' => 'warning',
                        'status_name' => 'Available',

                    ],
                ],
            ];

            array_push($count_dashboard, $events_array, $promotion_array, $products_array, $career_array, $service_bookings_array);

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'count_dashboard' => $count_dashboard]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch user details using Slug.
     *
     * @author: Stalvin M
     *
     * @created-on: 23 Frb, 2026
     *
     * @updated-on: N/A
     */
    public function updateProfile(Request $request)
    {
        Log::info($request->institution_id);
        Log::info($request->isLeadPharmacist);
        $institution_id = $request->institution_id;
        $wholesaler = $request->wholesaler;
        DB::beginTransaction();

        try {

            //  1. FIND USER

            $user = User::where('id', $request->profile_details['id'])->first();

            if (!$user) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            /*
            |-------------------------------------------------------
            | SET MODULE NAME BASED ON ROLE
            |-------------------------------------------------------
             */
            $moduleName = $user->rolename === 'Prescriber'
            ? 'Prescriber Registration'
            : ($user->rolename === 'Pharmacist'
                ? 'Pharmacist Registration'
                : 'User Management');

            /*
            |-------------------------------------------------------
            | USER OLD DATA
            |-------------------------------------------------------
             */
            $oldUser = $user->getOriginal();

            // 2. UPDATE USER
            $user->update([
                'name' => $request->profile_details['name'],
                'lastname' => $request->profile_details['lastname'],
                'email' => $request->profile_details['email'],
                'email_subscription' => $request->profile_details['email_subscription'],
                'role_id' => $request->profile_details['role_id'],
                'role_id' => $request->profile_details['role_id'],
                'updated_by' => $user->id,
            ]);
            // Added by Raghavendra to update pharmacist role
            if (isset($request->isLeadPharmacist)) {
                $roleValue = $request->isLeadPharmacist
                ? 'Lead pharmacist'
                : 'Pharmacist';

                $record = PharmacistDetails::where('user_id', $user->id)
                    ->where('institution_id', $institution_id)
                    ->first();
                if ($record) {

                    $oldData = $record->getOriginal();
                    $record->update([
                        'role' => $roleValue,
                        'updated_by' => $user->id,
                    ]);

                    $newData = $record->fresh()->toArray();

                    CustomFunctions::audit(
                        module: $moduleName,
                        action: 'UPDATE',
                        referenceId: $record->id,
                        referenceTable: 'pharmacist_details',
                        oldValues: $oldData,
                        newValues: $newData,
                        description: 'Pharmacist role updated'
                    );
                }

            }
            // Added by Raghavendra to update pharmacist role

            $newUser = $user->fresh()->toArray();

            /*
            |-------------------------------------------------------
            | USER AUDIT (MAIN)
            |-------------------------------------------------------
             */
            if (!empty(array_diff_assoc($newUser, $oldUser))) {

                CustomFunctions::audit(
                    module: $moduleName,
                    action: 'UPDATE',
                    referenceId: $user->id,
                    referenceTable: 'users',
                    oldValues: $oldUser,
                    newValues: $newUser,
                    description: $moduleName . ' profile updated'
                );
            }

            /*
            |-------------------------------------------------------
            | 3. HANDLE PRESCRIBER
            |-------------------------------------------------------
             */
            if ($user->rolename === 'Prescriber') {

                if ($request->has('hospitals')) {

                    foreach ($request->hospitals as $hospital) {

                        if (isset($hospital['id'])) {

                            // OLD DATA
                            $record = PrescriberDetails::where('id', $hospital['id'])
                                ->where('user_id', $user->id)
                                ->first();

                            if ($record) {

                                $oldData = $record->getOriginal();

                                // UPDATE
                                $record->update([
                                    'institution_id' => $hospital['institution_id'],
                                    'updated_by' => $user->id,
                                ]);

                                $newData = $record->fresh()->toArray();

                                // AUDIT UPDATE
                                CustomFunctions::audit(
                                    module: $moduleName,
                                    action: 'UPDATE',
                                    referenceId: $record->id,
                                    referenceTable: 'prescriber_details',
                                    oldValues: $oldData,
                                    newValues: $newData,
                                    description: 'Prescriber institution updated'
                                );
                            }

                        } else {

                            $existingRecord = PrescriberDetails::where('user_id', $user->id)->first();

                            // CREATE
                            $newRecord = PrescriberDetails::create([
                                'user_id' => $user->id,
                                'institution_id' => $hospital['institution_id'],
                                'reg_no' => $existingRecord->reg_no ?? null,
                                'job_title' => $existingRecord->job_title ?? null,
                                'created_by' => $user->id,
                            ]);

                            // AUDIT CREATE
                            CustomFunctions::audit(
                                module: $moduleName,
                                action: 'CREATE',
                                referenceId: $newRecord->id,
                                referenceTable: 'prescriber_details',
                                oldValues: null,
                                newValues: $newRecord->toArray(),
                                description: 'Prescriber institution created'
                            );
                        }
                    }
                }

                /*
                |------------------------------
                | PRESCRIBER MEDICATIONS
                |------------------------------
                 */

                // if ($request->has('drugs')) {

                //     foreach ($request->drugs as $drug) {

                //         if (isset($drug['id'])) {

                //             PrescriberMedication::where('id', $drug['id'])
                //                 ->update([
                //                     'drug_id'    => $drug['drug_id'],
                //                     'start_date' => $drug['start_date'],
                //                     'end_date'   => $drug['end_date'],
                //                     'updated_by' => $user->id,
                //                 ]);

                //         } else {

                //             $prescriberDetail = PrescriberDetails::where('user_id', $user->id)->first();

                //             PrescriberMedication::create([
                //                 'user_id'       => $user->id,
                //                 'prescriber_id' => $prescriberDetail->id ?? null,
                //                 'drug_id'       => $drug['drug_id'],
                //                 'start_date'    => $drug['start_date'],
                //                 'end_date'      => $drug['end_date'],
                //                 'is_check'      => 1,
                //                 'expired'       => 0,
                //                 'created_by'    => $user->id,
                //             ]);
                //         }
                //     }
                // }
            }

            /*
            |-------------------------------------------------------
            | 4. HANDLE PHARMACIST
            |-------------------------------------------------------
             */
            if ($user->rolename === 'Pharmacist') {

                if ($request->has('hospitals')) {

                    foreach ($request->hospitals as $hospital) {

                        if (isset($hospital['id'])) {

                            $record = PharmacistDetails::where('id', $hospital['id'])
                                ->where('user_id', $user->id)
                                ->first();

                            if ($record) {

                                $oldData = $record->getOriginal();

                                // UPDATE
                                $record->update([
                                    'institution_id' => $hospital['institution_id'],
                                    'updated_by' => $user->id,
                                ]);

                                $newData = $record->fresh()->toArray();

                                // AUDIT UPDATE
                                CustomFunctions::audit(
                                    module: $moduleName,
                                    action: 'UPDATE',
                                    referenceId: $record->id,
                                    referenceTable: 'pharmacist_details',
                                    oldValues: $oldData,
                                    newValues: $newData,
                                    description: 'Pharmacist institution updated'
                                );
                            }

                        } else {

                            $existingRecord = PharmacistDetails::where('user_id', $user->id)->first();

                            // CREATE
                            $newRecord = PharmacistDetails::create([
                                'user_id' => $user->id,
                                'institution_id' => $hospital['institution_id'],
                                'reg_no' => $existingRecord->reg_no ?? null,
                                'phone_no' => $existingRecord->phone_no ?? null,
                                'dispensing_address' => $existingRecord->dispensing_address ?? null,
                                'delivery_address' => $existingRecord->delivery_address ?? null,
                                'ordering_address' => $existingRecord->ordering_address ?? null,
                                'institution_type' => $existingRecord->institution_type ?? null,
                                'role' => $existingRecord->role ?? null,
                                'created_by' => $user->id,
                            ]);

                            // AUDIT CREATE
                            CustomFunctions::audit(
                                module: $moduleName,
                                action: 'CREATE',
                                referenceId: $newRecord->id,
                                referenceTable: 'pharmacist_details',
                                oldValues: null,
                                newValues: $newRecord->toArray(),
                                description: 'Pharmacist institution created'
                            );
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Profile updated successfully',
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

    public function fetchLockedUser(Request $request)
    {
        try {
            $usersdata = User::with('role')->where('is_locked', 'Y')->orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to update user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 14 Mar, 2026
     *
     * @updated-on: N/A
     */
    public function updateSuspiciousActor(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::with('role')->find($request->id);

            if (!$user) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'User not found',
                ]);
            }

            $return_message = trans('returnmessage.suspicious_actor_updated');

            // ================= OLD DATA =================
            $oldUserData = $user->toArray();
            unset($oldUserData['password']);

            $roleName = $user->role->rolename ?? null;

            $oldRoleData = match ($roleName) {
                'Pharmacist' => optional(PharmacistDetails::where('user_id', $user->id)->first())->toArray() ?? [],
                'Prescriber' => optional(PrescriberDetails::where('user_id', $user->id)->first())->toArray() ?? [],
                default => [],
            };

            $account_status = null;
            $oldMedications = [];
            $newMedications = [];

            // ================= MAIN LOGIC =================
            if ($user->status == 1 && $user->is_suspicious_actor == 0) {

                $account_status = 'De-Activated';

                // Update user
                $user->update([
                    'status' => 0,
                    'is_suspicious_actor' => 1,
                ]);

                // ================= MEDICATION HANDLING =================
                if ($roleName === 'Pharmacist') {

                    $oldMedications = PharmacistMedication::where('user_id', $user->id)
                        ->where('expired', 0)
                        ->get()
                        ->toArray();

                    PharmacistMedication::where('user_id', $user->id)
                        ->update([
                            'expired' => 1,
                            'expiry_reason' => 'User marked as Suspicious Actor',
                            'updated_by' => Auth::id(),
                        ]);
                }

                if ($roleName === 'Prescriber') {

                    $oldMedications = PrescriberMedication::where('user_id', $user->id)
                        ->where('expired', 0)
                        ->get()
                        ->toArray();

                    PrescriberMedication::where('user_id', $user->id)
                        ->update([
                            'expired' => 1,
                            'expiry_reason' => 'User marked as Suspicious Actor',
                            'updated_by' => Auth::id(),
                        ]);
                }

                if (!empty($oldMedications)) {
                    $newMedications = collect($oldMedications)->map(function ($item) {
                        $item['expired'] = 1;
                        $item['expiry_reason'] = 'User marked as Suspicious Actor';
                        return $item;
                    })->toArray();
                }
            }

            // ================= MODULE NAME =================
            $moduleName = match ($roleName) {
                'Pharmacist' => 'Pharmacist Registration',
                'Prescriber' => 'Prescriber Registration',
                'Nurse' => 'Nurses',
                'Admin' => 'Admin Update',
                default => $roleName . ' Update',
            };

            // ================= MEDICATION AUDIT =================
            if (!empty($oldMedications)) {

                $table = $roleName === 'Pharmacist'
                ? 'pharmacist_medications'
                : 'prescriber_medications';

                CustomFunctions::audit(
                    module: $moduleName,
                    action: 'UPDATE',
                    referenceId: $user->id,
                    referenceTable: $table,
                    oldValues: $oldMedications,
                    newValues: $newMedications,
                    description: 'Medications expired as user flagged as suspicious actor'
                );
            }

            // ================= NEW DATA =================
            $user->refresh();

            $newUserData = $user->toArray();
            unset($newUserData['password']);

            $newRoleData = match ($roleName) {
                'Pharmacist' => optional(PharmacistDetails::where('user_id', $user->id)->first())->toArray() ?? [],
                'Prescriber' => optional(PrescriberDetails::where('user_id', $user->id)->first())->toArray() ?? [],
                default => [],
            };

            // ================= USER AUDIT =================
            CustomFunctions::audit(
                module: $moduleName,
                action: 'UPDATE',
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: [
                    'user' => $oldUserData,
                    strtolower($roleName) => $oldRoleData,
                ],
                newValues: [
                    'user' => $newUserData,
                    strtolower($roleName) => $newRoleData,
                ],
                changedFields: array_keys($user->getChanges()),
                description: 'User marked as suspicious actor and account deactivated'
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => $return_message,
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

    public function updatePharmacistRole(Request $request)
    {
        try {
            $affected = PharmacistDetails::where('user_id', $request->user_id)
                ->update([
                    'role' => $request->is_lead ? 'Lead pharmacist' : 'Pharmacist',
                ]);

            if ($affected === 0) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'No pharmacists found',
                ]);
            }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.updatedsuccessfully'),
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
     * @function: to getUserWholesalers details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 22 April, 2026
     *
     * @updated-on: N/A
     */
    public function getUserWholesalers(Request $request)
    {
        try {

            // Step 1: get pharmacist_details ids
            $pharmacistIds = PharmacistDetails::where('user_id', $request->user_id)
                ->where('institution_id', $request->institution_id)
                ->pluck('id');

            // Step 2: get wholesalers mapped to those pharmacist_ids
            $wholesalers = PharmacistWholesaler::whereIn('pharmacist_id', $pharmacistIds)
                ->with('wholesaler') // assumes relation exists
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->wholesaler_id,
                        'acc_no' => $item->acc_no,
                        'name' => $item->wholesaler->name ?? null,
                    ];
                })
                ->unique('id')
                ->values();

            return response()->json([
                'status' => 'S',
                'message' => 'Data retrieved successfully',
                'wholesalers' => $wholesalers,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => 'Error processing request',
                'error_data' => $e->getMessage(),
            ]);
        }
    }

}
