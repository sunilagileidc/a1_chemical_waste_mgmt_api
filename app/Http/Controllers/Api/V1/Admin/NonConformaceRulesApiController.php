<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\NonConformanceRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Log;

class NonConformaceRulesApiController extends Controller
{
    /**
     * @function: to fetch Non Conformance details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 April 2026
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $nonconformancerules = NonConformanceRules::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'nonconformancerules' => $nonconformancerules]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store Non Conformance details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 April 2026
     *
     * @updated-on: N/A
     */

    public function store(Request $request)
    {

        Log::info('$request');
        Log::info($request);
        $validator = Validator::make($request->all(), [
            'conformance_type' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:1,0',
        ]);

        Log::info('$validator');
        Log::info($validator->errors()->all());

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {
            DB::beginTransaction();

            // ================= UPDATE =================
            if (!empty($request->id)) {

                $rule = NonConformanceRules::findOrFail($request->id);

                // Check duplicate (excluding current ID)
                $exists = NonConformanceRules::where('conformance_type', $request->conformance_type)
                    ->where('id', '!=', $request->id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'status' => 'E',
                        'message' => 'Conformance type already exists',
                    ]);
                }

                $rule->update([
                    'conformance_type' => $request->conformance_type,
                    'description' => $request->description,
                    'status' => $request->status,
                    'updated_by' => Auth::id(),
                ]);

                DB::commit();

                return response()->json([
                    'status' => 'S',
                    'message' => 'Updated successfully',
                    'data' => $rule,
                ]);
            }

            // ================= CREATE =================

            // Check duplicate
            $exists = NonConformanceRules::where('conformance_type', $request->conformance_type)->exists();

            if ($exists) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Conformance type already exists',
                ]);
            }

            $rule = NonConformanceRules::create([
                'conformance_type' => $request->conformance_type,
                'description' => $request->description,
                'status' => $request->status,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Created successfully',
                'data' => $rule,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Error processing request',
                'error_data' => $e->getMessage(),
            ], 500);
        }
    }

/**
 * @function: to Edit Non Conformance details.
 *
 * @author: Raghavendra kumar
 *
 * @created-on: 27 April 2026
 *
 * @updated-on: N/A
 */

    public function editNonConformanceRules($slug)
    {
        try {

            $nonconformancerules = NonConformanceRules::where('slug', $slug)
                ->firstOrFail();

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.data_return'),
                'nonconformancerules' => $nonconformancerules,
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
     * @function: to Update the Non Conformance status.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 April 2026
     *
     * @updated-on: N/A
     */
    public function updateNonConformanceStatus(request $request)
    {
        try {
            $nonconformancerules = NonConformanceRules::where('id', $request->id)->first();
            if ($nonconformancerules->status == 1) {
                $status = NonConformanceRules::where('id', $request->id)->update(['status' => 0]);
            } else {
                $status = NonConformanceRules::where('id', $request->id)->update(['status' => 1]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

}
