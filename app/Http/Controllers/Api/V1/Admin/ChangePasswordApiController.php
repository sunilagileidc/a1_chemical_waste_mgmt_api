<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class ChangePasswordApiController extends Controller
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
     * @function: to change the password.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function changePassword(Request $request)
    {
        try
        {
            $user = User::where('id', $request->user_id)->first();

            if (!Hash::check($request->currentpassword, $user->password)) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.current_password_mismatch')]);
            }

            if ($request->newpassword != $request->confirmpassword) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.password_mismatch')]);
            } else {
                $passwordhash = \Hash::make($request->newpassword);
                $user->password = $passwordhash;
                $user->save();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.password_updated_successful')]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error' => $e->getMessage()]);
        }
    }

}
