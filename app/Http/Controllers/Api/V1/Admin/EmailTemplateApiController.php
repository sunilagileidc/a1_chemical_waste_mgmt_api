<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\EmailTemplate;
use App\Models\LookUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Log;

class EmailTemplateApiController extends Controller
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
     * @function: to fetch EmailTemplate details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function index(Request $request)
    {
        try
        {
            $Emailtemplates = EmailTemplate::orderBy('created_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'email_templates' => $Emailtemplates]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store EmailTemplate details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        try
        {

            $templatetype = LookUp::where('id', $request->template_type_id)->value('shortname');
            $data = $request->all();

            $data['is_standard'] = 'N';

            if ($templatetype != 'Email') {
                $data['template_signature'] = '';
            } else {
                $data['template_signature'] = CustomFunctions::parseEmbeddedImageString($request->template_signature, 'email_template');
            }

            $data['template_body'] = CustomFunctions::parseEmbeddedImageString($request->template_body, 'email_template');
            $Emailtemplate = EmailTemplate::create($data);

            $status = 'S';
            $msg = trans('returnmessage.createdsuccessfully');
            return response()->json(['status' => $status, 'message' => $msg, 'emailtemplate' => $Emailtemplate]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to display EmailTemplate details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function show($slug)
    {
        try
        {
            $Emailtemplate = EmailTemplate::where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'email_template' => $Emailtemplate]);
            return response()->json($Emailtemplate);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update EmailTemplate details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $slug)
    {
        try
        {
            $templatetype = LookUp::where('id', $request->template_type_id)->value('shortname');
            $data = $request->all();

            if ($templatetype != 'Email') {
                $data['template_signature'] = '';
            } else {
                $data['template_signature'] = CustomFunctions::parseEmbeddedImageString($request->template_signature, 'email_template');
            }

            $data['template_body'] = CustomFunctions::parseEmbeddedImageString($request->template_body, 'email_template');

            $Emailtemplate = EmailTemplate::where('slug', $request->slug)->firstOrFail();
            $Emailtemplate->update($data);

            $status = 'S';
            $msg = trans('returnmessage.updatedsuccessfully');
            return response()->json(['status' => $status, 'message' => $msg, 'role' => $Emailtemplate]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete EmailTemplate details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try
        {
            EmailTemplate::destroy($id);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch EmailTemplate type details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function emailTemplateTypes($templatetype)
    {
        try {
            $parentid = LookUp::where('shortname', 'TEMPLATE_TYPE')->get()->first();
            $templateid = LookUp::where('shortname', 'like', $templatetype . '%')->where('parentid', $parentid->id)->get()->first();
            $Emailtemplates = EmailTemplate::where('template_type_id', 'like', $templateid->id)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.error'), 'emailtemplates' => $Emailtemplates]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch other emailTemplate details.
     *
     * @author: Santhosha G
     *
     * @created-on: 04 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function otherEmailTemplates()
    {
        try {
            $EmailTemplate = EmailTemplate::get();
            return response()->json($EmailTemplate);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
