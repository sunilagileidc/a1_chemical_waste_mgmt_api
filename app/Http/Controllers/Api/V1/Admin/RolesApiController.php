<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;

class RolesApiController extends Controller
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
     * @function: to fetch Role details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try
        {
            $roles = Role::orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'role' => $roles]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store Role details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        try
        {
            $currenttime = date('Y-m-d h:i:s');

            if (Role::where('role_display_name', $request->rolename)->count() > 0) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.role') . $request->rolename . trans('returnmessage.exists')]);
            }
            $Role = Role::create([
                'rolename' => $request->rolename,
                'role_display_name' => $request->rolename,
                'roledescription' => $request->roledescription,
                'created_at' => $currenttime,
            ]);
            CustomFunctions::updateSlug($Role->id, $request->rolename, 'roles');

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'role' => $Role]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit Role details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function edit($slug)
    {
        try
        {
            $Role = Role::where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'role' => $Role]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update Role details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $id)
    {
        try
        {
            if (Role::where('rolename', $request->rolename)->where('id', '!=', $id)->count() > 0) {
                return response()->json(['status' => 'E', 'message' => 'Role ' . $request->rolename . ' already exists.']);
            }
            $Role = Role::findOrFail($id);
            $Role->update($request->all());
            CustomFunctions::updateSlug($Role->id, $request->rolename, 'roles');

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'role' => $Role]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete Role details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try
        {
            Role::destroy($id);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_delete')]);
        }
    }

    /**
     * @function: to fetch Role details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchRole(Request $request)
    {
        try {
            $roles = Role::get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'roles' => $roles]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function fetchRegRoles(Request $request)
    {
        try {
            $roles = Role::whereIn('rolename', ['StoreAdmin', 'MallAdmin'])->get(['id', 'rolename', 'role_display_name']);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'roles' => $roles]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
