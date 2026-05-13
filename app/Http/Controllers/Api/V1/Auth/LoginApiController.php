<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Log;

class LoginApiController extends Controller
{
    public function __construct(Request $request)
    {
        $locale = $request->input('lang');
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }
        App::setLocale($locale);
    }
    /**
     * @function: to login.
     *
     * @author: Suprith S
     *
     * @created-on: 1 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function login(Request $request)
    {
        try {
            Log::info("login =================");
            Log::info($request);
            $request->validate([
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            $credentials = request(['email', 'password']);
            if (!Auth::attempt($credentials)) {
                return response()->json([
                    'status' => 'E',
                    'error' => trans('auth.failed'),
                    'message' => trans('auth.failed'),
                ], 401);
            }

            $user = $request->user();
            if ($user) {
                if ($user->status == 0) {
                    return response()->json([
                        'status' => 'E',
                        'error' => trans('auth.failed'),
                        'message' => trans('auth.failed'),
                    ], 401);
                }
            }
            if ($request->role == 'User') {
                if ($user->rolename !== 'User') {
                    return response()->json([
                        'status' => 'E',
                        'error' => trans('auth.failed'),
                        'message' => trans('auth.failed'),
                    ], 401);
                }
            }
            if (!$request->role) {
                if ($user->rolename == 'User') {
                    return response()->json([
                        'status' => 'E',
                        'error' => trans('auth.failed'),
                        'message' => trans('auth.failed'),
                    ], 401);
                }
            }
            $tokenResult = $user->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $userdata = User::with('role')->where('id', $user->id)->first();
            $token->save();

            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'status' => 'S',
                'expires_at' => Carbon::parse(
                    $tokenResult->token->expires_at
                )->toDateTimeString(),
                'userdata' => $userdata,

            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @author: Suprith S
     *
     * @created-on: 1 Dec, 2022
     *
     * @updated-on: N/A
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        try {
            Log::info($request);
            $request->user()->token()->revoke();
            return response()->json([
                'message' => trans('auth.logout'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * Get the authenticated User
     *
     * @author: Suprith S
     *
     * @created-on: 1 Dec, 2022
     *
     * @updated-on: N/A
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        try {
            return response()->json($request->user());
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to send password reset details to mail.
     *
     * @author: Suprith S
     *
     * @created-on: 1 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function sendPasswordReset(Request $request)
    {
        try {
            $otp = rand(100000, 999999);
            $currenttime = date('Y-m-d h:i:s');
            $otptime = strtotime($currenttime . ' + 5 minute');
            $otptime = date('Y-m-d h:i:s', $otptime);
            $otphash = \Hash::make($otp);

            $updateOtp = User::where('email', $request->email)
                ->update(['otp' => $otphash,
                    'otp_valid_until' => $otptime]);

            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.credentials_mismatch')]);
            }
            $emailTemplate = EmailTemplate::where('template_name', 'Forgot Password')->first();
            if (isset($emailTemplate)) {
                $actionText = null;
                $actionUrl = null;
                $userdata = ['firstname' => $user->name . ' ' . $user->lastname, 'otp' => $otp];
                $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                Mail::to($request->email)->send(new UserRegistration($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.password_reset_email_sent')]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing')]);
        }
    }

    /**
     * @function: to Customer login.
     *
     * @author: Santhosha
     *
     * @created-on: 08 Mar, 2024
     *
     * @updated-on: N/A
     */
    public function customerLogin(Request $request)
    {
        try {
            $request->validate([
                'phone' => 'required|string',
                'password' => 'required|string',
            ]);

            $customer = Customer::where('phone', $request->phone)->first();

            if (!$customer || !Hash::check($request->password, $customer->password)) {
                return response()->json([
                    'status' => 'E',
                    'error' => trans('auth.failed'),
                    'message' => trans('auth.failed'),
                ], 401);
            }


            if ($customer->status == 0) {
                return response()->json([
                    'status' => 'E',
                    'error' => trans('auth.failed'),
                    'message' => trans('auth.failed'),
                ], 401);
            }

            $tokenResult = $customer->createToken('Personal Access Token');
            $token = $tokenResult->token;
            $token->save();

            return response()->json([
                'access_token' => $tokenResult->accessToken,
                'token_type' => 'Bearer',
                'status' => 'S',
                'expires_at' => Carbon::parse($tokenResult->token->expires_at)->toDateTimeString(),
                'customer_data' => $customer,
            ]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getMessage()]);
        }
    }

}
