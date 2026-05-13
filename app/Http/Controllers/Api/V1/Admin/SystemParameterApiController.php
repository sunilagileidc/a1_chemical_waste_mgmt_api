<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemParameters;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SystemParameterApiController extends Controller
{
    /**
     * @function: to fetch System Parameters details.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $systemparameters = SystemParameters::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'systemparameters' => $systemparameters]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch System Parameters details.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchsystemparameter()
    {
        try {
            $systemparameters = SystemParameters::where('parameter_name', 'PROPERTY_FEATURES')->first();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'systemparameters' => $systemparameters]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch System Parameters Image URL details.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchImageUrl()
    {
        try {
            $app_name = '';
            $systemparameters = SystemParameters::where('parameter_name', 'APP_LOGO')->where('status', 1)->first();
            $login_otp_enabled = SystemParameters::where('parameter_name', 'LOGIN_OTP_ENABLED')->first();
            $app_name = config('values.APP_NAME');
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'parameter_image' => $systemparameters, 'application_name' => $app_name, 'login_otp_enabled' => $login_otp_enabled]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update System Parameters status.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function updateSystemParamStatus(request $request)
    {
        try {
            $SystemParameters = SystemParameters::where('id', $request->id)->first();
            if ($SystemParameters->status == 1) {
                $status = SystemParameters::where('id', $request->id)->update(['status' => 0]);
            } else {
                $status = SystemParameters::where('id', $request->id)->update(['status' => 1]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit System Parameters details.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function editSystemParameter($slug)
    {
        try {
            $systemparameter = SystemParameters::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'systemparameter' => $systemparameter]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store System Parameters details.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        $currenttime = date('Y-m-d h:i:s');
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'parameter_name' => 'required',
            'parameter_value' => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($request->parameter_name) {
                $exists = SystemParameters::where('id', '!=', $request->id)->where('parameter_name', $request->parameter_name)->first();
                if ($exists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.already_exists')]);
                }
            }

            if ($id > 0) {
                $systemparameter = SystemParameters::where('id', $id)
                    ->update([
                        'parameter_name' => $request->parameter_name,
                        'parameter_value' => $request->parameter_value,
                        'description' => $request->description,
                        'is_file_upload' => $request->is_file_upload,
                        'updated_at' => $currenttime,
                    ]);
                $systemparameter = SystemParameters::where('id', $request->id)->first();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'systemparameter' => $systemparameter]);
            } else {
                $systemparameter = SystemParameters::create([
                    'parameter_name' => $request->parameter_name,
                    'parameter_value' => $request->parameter_value,
                    'description' => $request->description,
                    'is_file_upload' => $request->is_file_upload,
                    'created_at' => $currenttime,
                ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'systemparameter' => $systemparameter]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete System Parameters details.
     *
     * @author: Santhosha G
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function deleteSystemParameter($id)
    {
        try {
            $deletesystemparameter = SystemParameters::where('id', $id)->delete();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function getSystemParameter(Request $request)
    {
        try {
            $parameter = SystemParameters::where('parameter_name', $request->parameter_name)->value('parameter_value');

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'parameter' => $parameter]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
