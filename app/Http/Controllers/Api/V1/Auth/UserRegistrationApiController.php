<?php
namespace App\Http\Controllers\Api\V1\Auth;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Jobs\UserRegistration;
use App\Mail\UserRegistrationMail;
use App\Models\EmailTemplate;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;
use Mail;

class UserRegistrationApiController extends Controller
{
    public function __construct(Request $request)
    {
        $locale = $request->input('lang');
        if (! in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }
        App::setLocale($locale);
    }
    /**
     * @function: to Register users details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function userRegistration(Request $request)
    {
        try {
            $otp         = mt_rand(100000, 999999);
            $currenttime = date('Y-m-d h:i:s');
            $otptime     = strtotime($currenttime . ' + 5 minute');
            $otptime     = date('Y-m-d h:i:s', $otptime);
            $otphash     = \Hash::make($otp);

            $registrationexists = User::where('email', $request->email)->where('is_otp_validated', 0)->first();
            $password           = $request->password;
            $password           = Hash::make($password);
            $role_id            = $request->role_id;

            DB::beginTransaction();
            if ($registrationexists) {
                $users = User::where('email', $request->email)
                    ->update([
                        'salutation'       => $request->salutation,
                        'name'             => Str::ucfirst($request->name),
                        'lastname'         => Str::ucfirst($request->lastname),
                        'gender'           => $request->gender,
                        'email'            => $request->email,
                        'password'         => $password,
                        'role_id'          => $role_id,
                        'store_id'         => $request->store_id,
                        'is_otp_validated' => 0,
                        'otp_valid_until'  => $otptime,
                        'otp'              => $otphash,
                        'status'           => 1,
                    ]);

            } else {
                $userexists = User::where('email', $request->email)->first();

                if ($userexists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_already_exists')]);
                }
                $users = User::create([
                    'salutation'       => $request->salutation,
                    'name'             => Str::ucfirst($request->name),
                    'lastname'         => Str::ucfirst($request->lastname),
                    'gender'           => $request->gender,
                    'email'            => $request->email,
                    'password'         => $password,
                    'role_id'          => $role_id,
                    'store_id'         => $request->store_id,
                    'is_otp_validated' => 0,
                    'otp_valid_until'  => $otptime,
                    'otp'              => $otphash,
                    'status'           => 0,
                ]);

            }
            $emailTemplate    = EmailTemplate::where('template_name', 'OTP Verification')->first();
            $actionText       = null;
            $actionUrl        = null;
            $userdata         = ['firstname' => $request->name, 'email' => $request->email, 'otp' => $otp];
            $parsedSubject    = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
            $parsedContent    = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
            $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
            Mail::to($request->email)->send(new UserRegistrationMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.registration_mail_sent'), 'users' => $users]);
        } catch (\Exception $e) {
            Log::info($e);
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

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
            'otp'   => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }
        try {
            $otp  = \Hash::make((int) $request->otp);
            $user = User::where('email', $request->email)
                ->first();
            $currenttime = date('Y-m-d h:i:s');
            if ($user) {

                if ($currenttime > $user->otp_valid_until) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
                }
                if (! Hash::check($request->otp, $user->otp)) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.invalid_verification_code')]);
                } else {
                    $user->otp              = null;
                    $user->status           = 1;
                    $user->is_otp_validated = 1;
                    $user->otp_valid_until  = null;
                    $user->save();

                    $userid       = $user->id;
                    $logtype      = 'Registration';
                    $title        = 'Registration Successful';
                    $description  = $user->name . ' ' . $user->lastname . ' Registered Successfully';
                    $createdby    = $user->id;
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
}
