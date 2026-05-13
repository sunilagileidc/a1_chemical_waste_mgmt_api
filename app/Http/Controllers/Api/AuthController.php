<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\CustomClass\CustomFunctions;
use App\Models\EmailTemplate;
use App\Models\User;
use App\Models\Countries;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Location\Facades\Location;
use App\Mail\UserRegistrationMail;
use Mail;
use Log;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $access_token = $user->createToken('API Token')->accessToken;

        return response()->json([
            'status' => 'S',
            'access_token' => $access_token,
            'user' => $user,
        ]);
    }

    private function validateCountryAccess(Request $request)
    {
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
        if (!$countryCode && app()->environment('local')) {
            $countryCode = 'IN';
            $countryName = 'INDIA';
        }

        // IP fallback
        if (!$countryCode) {
            $ip = $request->ip();
            $location = \Location::get($ip);

            if ($location && $location->countryCode) {
                $countryCode = $location->countryCode;
                $countryName = $location->countryName;
            }
        }

        // Not detected
        if (!$countryCode) {
            return [
                'success' => false,
                'message' => 'Unable to detect location'
            ];
        }

        // Not allowed
        if (!in_array($countryCode, $allowedCountries)) {
            return [
                'success' => false,
                'message' => 'Login not allowed from your country',
                'country' => $countryName
            ];
        }

        // success
        return [
            'success' => true,
            'country_code' => $countryCode,
            'country_name' => $countryName
        ];
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
            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json([
                    'status' => 'E',
                    'message' => trans('returnmessage.invalid_credentials'),
                ], 401);
            }

            $countryCheck = $this->validateCountryAccess($request);

            if (!$countryCheck['success']) {
                return response()->json([
                    'status' => 'E',
                    'message' => $countryCheck['message'],
                    'country' => $countryCheck['country'] ?? null
                ], 403);
            }

            $countryName = $countryCheck['country_name'];

            // Sending OTP to user
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
            $emailTemplate = EmailTemplate::where('template_name', 'Login OTP Verification')->first();

            if (isset($emailTemplate)) {
                $actionText = null;
                $actionUrl = null;
                $userdata = ['firstname' => $user->name . ' ' . $user->lastname, 'otp' => $otp];
                $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                Mail::to($request->email)->send(new UserRegistrationMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.login_otp_email_sent')]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function login(Request $request)
    {
        // Authenticate user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.invalid_credentials'),
            ], 401);
        }
        $countryCheck = $this->validateCountryAccess($request);

        if (!$countryCheck['success']) {
            return response()->json([
                'status' => 'E',
                'message' => $countryCheck['message'],
                'country' => $countryCheck['country'] ?? null
            ], 403);
        }

        $countryName = $countryCheck['country_name'];

        $user = Auth::user();

        /**
         * 7 Revoke old tokens
         */
        $user->tokens()->delete();

        /**
         * 8 Create new token
         */
        $tokenResult = $user->createToken('API Token');
        $accessToken = $tokenResult->accessToken;
        $tokenId = $tokenResult->token->id;

        /**
         * 9 Save token id only
         */
        $user->token_id = $tokenId;
        $user->save();

        return response()->json([
            'status' => 'S',
            'access_token' => $accessToken,
            'user' => $user,
            'country' => $countryName,
        ]);
    }

    public function profile(Request $request)
    {
        return response()->json(Auth::user());
    }

    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 'S',
            'message' => 'Logged out successfully',
        ]);
    }
}
