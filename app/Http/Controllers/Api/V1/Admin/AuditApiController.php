<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;

class AuditApiController extends Controller
{
    /**
     * @function: to fetch audit data.
     *
     * @author: Santhosha G
     *
     * @created-on: 07 Mar 2026
     *
     * @updated-on: 07 Mar 2026
     */
    public function index()
    {
        try {

            $audit = Audit::with('user')->orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'audit' => $audit]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
