<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
use App\Models\Countries;
use App\Models\EmailTemplate;
use App\Models\LoginAudit;
use App\Models\SystemParameters;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Log;
use Mail;

class LoginApiController extends Controller
{
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string',
                'email'    => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $access_token = $user->createToken('API Token')->accessToken;

            return response()->json([
                'status'       => 'S',
                'access_token' => $access_token,
                'user'         => $user,
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: Valdation country access.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Feb, 2026
     *
     * @updated-on: 09 Feb, 2026
     */
    private function validateCountryAccess(Request $request, $user)
    {
        try {

            $allowedCountries = Countries::where('is_whitelisted', 1)
                ->pluck('country_code')
                ->toArray();

            $countryCode = null;
            $countryName = null;

            // Cloudflare
            if ($request->hasHeader('CF-IPCountry')) {
                $countryCode = $request->header('CF-IPCountry');
                $countryName = $countryCode;
            }

            // Localhost
            if (! $countryCode && app()->environment('local')) {
                $countryCode = 'IN';
                $countryName = 'INDIA';
            }

            // IP fallback
            if (! $countryCode) {
                $ip       = $request->ip();
                $location = \Location::get($ip);

                if ($location && $location->countryCode) {
                    $countryCode = $location->countryCode;
                    $countryName = $location->countryName;
                }
            }

            // Not detected
            if (! $countryCode) {
                return [
                    'success' => false,
                    'message' => 'Unable to detect location',
                ];
            }

            // Not allowed
            if (! in_array($countryCode, $allowedCountries)) {
                return [
                    'success' => false,
                    'message' => 'Login not allowed from your country',
                    'country' => $countryName,
                ];
            }

            $ipAddress = $request->header('CF-Connecting-IP') ?? $request->ip();

            LoginAudit::create([
                'user_id'      => $user->id,
                'created_by'   => $user->id,
                'ip_address'   => $ipAddress,
                'country_code' => $countryCode,
                'country_name' => $countryName,
                'user_agent'   => $request->userAgent(),
            ]);

            // success
            return [
                'success'      => true,
                'country_code' => $countryCode,
                'country_name' => $countryName,
            ];
        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: To send Password Reset.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Feb, 2026
     *
     * @updated-on: 09 Feb, 2026
     */
    public function sendLoginOtp(Request $request)
    {
        try {
            // Authenticate user
            if (! Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status'  => 'E',
                    'message' => trans('returnmessage.invalid_credentials'),
                ], 401);
            }

            $user = User::where('email', $request->email)->first();

            $countryCheck = $this->validateCountryAccess($request, $user);

            if (! $countryCheck['success']) {
                return response()->json([
                    'status'  => 'E',
                    'message' => $countryCheck['message'],
                    'country' => $countryCheck['country'] ?? null,
                ], 403);
            }

            $countryName = $countryCheck['country_name'];

            // Sending OTP to user
            $otp         = rand(100000, 999999);
            $currenttime = date('Y-m-d h:i:s');
            $otptime     = strtotime($currenttime . ' + 5 minute');
            $otptime     = date('Y-m-d h:i:s', $otptime);
            $otphash     = \Hash::make($otp);

            $updateOtp = User::where('email', $request->email)
                ->update(['otp'   => $otphash,
                    'otp_valid_until' => $otptime]);

            if (! $user) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
            }
            $emailTemplate = EmailTemplate::where('template_name', 'Login OTP Verification')->first();

            if (isset($emailTemplate)) {
                if (
                    $emailTemplate->is_mandatory === 1 ||
                    ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                ) {
                    $actionText       = null;
                    $actionUrl        = null;
                    $userdata         = ['firstname' => $user->full_name, 'otp' => $otp];
                    $parsedSubject    = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                    $parsedContent    = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                    Mail::to($request->email)->queue(new RegistrationRejectionMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.login_otp_email_sent')]);
                } else {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_unsubscription')]);
                }
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_template_unavailable')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: To login.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Feb, 2026
     *
     * @updated-on: 09 Feb, 2026
     */
    public function login(Request $request)
    {

        try {
            // 1️ Validate
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);
            // 2️ Get user first (avoid double queries)
            $user = User::with('pharmacist')->where('email', $request->email)->first();

            if (! $user) {
                // Check if account is locked
                return response()->json([
                    'status'  => 'E',
                    'message' => trans('returnmessage.invalid_credentials'),
                ], 401);
            }

            if ($user->is_locked === 'Y') {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Your account is locked. Please contact administrator.',
                ], 423); // 423 Locked
            }

            // 3️ Check password manually (faster & cleaner)
            if (! Hash::check($request->password, $user->password)) {

                // Increase failed login attempt count
                $user->increment('password_count');

                $systemparameters = SystemParameters::where('parameter_name', 'USER_LOCK_ATTEMPT_LIMIT')->where('status', 1)->value('parameter_value');

                $maxAttempts       = $systemparameters;
                $remainingAttempts = $maxAttempts - $user->password_count;

                // If max attempts reached → lock account
                if ($user->password_count >= $maxAttempts) {

                    $oldData = $user->toArray();

                    $user->update([
                        'is_locked' => 'Y',
                    ]);

                    $newData = $user->fresh()->toArray();

                    CustomFunctions::audit(
                        module: 'User Management',
                        action: 'LOCK USER',
                        referenceId: $user->id,
                        referenceTable: 'users',
                        oldValues: $oldData,
                        newValues: $newData,
                        changedFields: ['is_locked'],
                        description: "User account locked automatically after exceeding maximum failed login attempts for {$user->full_name}."
                    );

                    return response()->json([
                        'status'   => 'E',
                        'message'  => 'Your account has been locked due to multiple failed login attempts.',
                        'attempts' => 0,
                    ], 423);
                }

                return response()->json([
                    'status'  => 'E',
                    'message' => "Invalid password. You have {$remainingAttempts} attempt(s) remaining.",
                    'attempts' => $remainingAttempts,
                ], 401);
            }

            // Reset failed attempts on successful login
            $user->update([
                'password_count' => 0,
                'is_locked'      => 'N',
            ]);

            // 4️ Check registration status FIRST
            if ($user->reg_status === 'Awaiting Approval') {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Your account is currently under review. You will be notified once it has been approved.',
                ], 403);
            }

            if ($user->reg_status === 'Rejected') {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Your registration was not approved. Please contact the administrator for further assistance.',
                ], 403);
            }

            // 5 Check inactive separately (as you wanted)
            if ($user->status != 1) {
                if ($user->expired == 1) {
                    return response()->json([
                        'status'  => 'E',
                        'message' => trans('returnmessage.account_inactive_for_6_months'),
                    ], 403);
                } else {
                    return response()->json([
                        'status'  => 'E',
                        'message' => trans('returnmessage.account_inactive'),
                    ], 403);
                }

            }

            // 6 Country validation
            $countryCheck = $this->validateCountryAccess($request, $user);

            if (! $countryCheck['success']) {
                return response()->json([
                    'status'  => 'E',
                    'message' => $countryCheck['message'],
                    'country' => $countryCheck['country'] ?? null,
                ], 403);
            }

            $countryName = $countryCheck['country_name'];

            /**
             * 7 Revoke old tokens (FASTER than delete)
             */
            // $user->tokens()->update(['revoked' => 1]);

            /**
             * 8 Create new token
             */
            $tokenResult = $user->createToken('API Token');

            $token = $tokenResult->token;

            $token->forceFill([
                'expires_at' => now()->addHours(12),
            ])->save();

            $tokenId = $token->id;

            /**
             * 9 Update token_id without extra model load
             */
            $user->update([
                'token_id' => $tokenId,
            ]);

            CustomFunctions::audit(
                module: 'Authentication',
                userId: $user->id,
                action: 'LOGIN',
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: null,
                newValues: [
                    'email' => $user->email,
                ],
                changedFields: null,
                description: 'User logged into system',
                status: 'SUCCESS'
            );

            return response()->json([
                'status'       => 'S',
                'access_token' => $tokenResult->accessToken,
                'expires_at'   => $token->expires_at,
                'user'         => $user,
                'country'      => $countryName,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function profile(Request $request)
    {
        try {
            return response()->json(Auth::user());
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: To logout.
     *
     * @author: Santhosha G
     *
     * @created-on: 09 Mar, 2026
     *
     * @updated-on: 09 Mar, 2026
     */
    public function logout(Request $request)
    {
        try {
            CustomFunctions::audit(
                module: 'Authentication',
                action: 'LOGOUT',
                referenceId: $request->id,
                referenceTable: 'users',
                oldValues: null,
                newValues: [
                    'email' => $request->email,
                ],
                changedFields: null,
                description: 'User logged out from system',
                status: 'SUCCESS'
            );

            $request->user()->token()->revoke();

            return response()->json([
                'status'  => 'S',
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: To unlock the locked user.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar, 2026
     *
     * @updated-on: 05 Mar, 2026
     */
    public function unlockUser(Request $request)
    {
        try {
            DB::beginTransaction();

            $user = User::findOrFail($request->id);

            $oldData = $user->toArray();

            $user->update([
                'is_locked'          => 'N',
                'slpassword_countug' => 0,
            ]);

            $newData = $user->fresh()->toArray();

            CustomFunctions::audit(
                module: 'User Management',
                action: 'UNLOCK',
                referenceId: $user->id,
                referenceTable: 'users',
                oldValues: $oldData,
                newValues: $newData,
                changedFields: ['is_locked', 'slpassword_countug'],
                description: "User account '{$user->full_name}' (ID: {$user->id}, Email: {$user->email}) was unlocked."
            );

            DB::commit();

            return response()->json([
                'status'  => 'S',
                'message' => 'User unlocked successfully.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'E','message'    => trans('returnmessage.error_processing'), 'error_data' => $e->getMessage()]);
        }
    }

    /**
     * @function: for retrospective auth.
     *
     * @author: Stalvin M
     *
     * @created-on: 17 April, 2026
     *
     * @updated-on: 17 April, 2026
     */
    public function verifyRetrospective(Request $request)
    {
        try {
            $request->validate([
                'password' => 'required|string',
            ]);

            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'User not authenticated.',
                    'valid'   => false,
                ], 401);
            }

            if (! Hash::check($request->password, $user->password)) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Incorrect password. Please try again.',
                    'valid'   => false,
                ], 200);
            }

            return response()->json([
                'status'  => 'S',
                'message' => 'Password verified successfully.',
                'valid'   => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => 'Something went wrong while verifying password.',
                'valid'   => false,
            ], 500);
        }
    }
}
