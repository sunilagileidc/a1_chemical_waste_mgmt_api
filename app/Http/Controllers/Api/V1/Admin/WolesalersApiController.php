<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\PharmacistDetails;
use App\Models\PharmacistWholesaler;
use App\Models\Wholesalers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Log;

class WolesalersApiController extends Controller
{
    /**
     * @function: to fetch drugs details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 19 Feb 2026
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $wholesalers = Wholesalers::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'wholesalers' => $wholesalers]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to store wholesaler details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 19 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();
            if (Wholesalers::where('email', $request->email)->count() > 0) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.menu') . ' ' . $request->email . ', ' . trans('returnmessage.email_already_exists')]);
            }

            $wholesalers = Wholesalers::create($request->all());

            // AUDIT
            CustomFunctions::audit(
                module: 'Wholesalers',
                action: 'CREATE',
                referenceId: $wholesalers->id,
                referenceTable: 'wholesalers',
                newValues: $wholesalers->toArray(),
                description: "New Wholesaler '{$wholesalers->name}' created with email '{$wholesalers->email}'."
            );

            DB::commit();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'wholesalers' => $wholesalers]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }
    /**
     * @function: to edit wholesaler details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 19 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function edit($slug)
    {
        try {
            $wholesaler = Wholesalers::where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'wholesaler' => $wholesaler]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }
    /**
     * @function: to udpate wholesaler details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 19 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $wholesaler = Wholesalers::findOrFail($id);

            $oldData = $wholesaler->toArray();

            $wholesaler->update($request->all());

            $newData = $wholesaler->fresh()->toArray();

            // AUDIT
            CustomFunctions::audit(
                module: 'Wholesalers',
                action: 'UPDATE',
                referenceId: $wholesaler->id,
                referenceTable: 'wholesalers',
                oldValues: $oldData,
                newValues: $newData,
                changedFields: array_keys($wholesaler->getChanges()),
                description: "Wholesaler '{$wholesaler->name}' details updated."
            );

            DB::commit();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'wholesaler' => $wholesaler]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch active drugs.
     *
     * @author: Stalvin M
     *
     * @created-on: 19 Feb 2026
     *
     * @updated-on: N/A
     */
    public function fetchActiveWholesalers()
    {
        try {
            $wholesalers = Wholesalers::where("status", 1)->orderBy("id", "desc")->get(["id", "name"]);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'wholesalers' => $wholesalers]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to Update the drug status.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 10 March 2026
     *
     * @updated-on: N/A
     */
    public function updateWholesalerStatus(Request $request)
    {
        try {
            DB::beginTransaction();

            $wholesaler = Wholesalers::findOrFail($request->id);

            $oldData = $wholesaler->toArray();

            // Toggle status
            $wholesaler->status = $wholesaler->status == 1 ? 0 : 1;
            $wholesaler->save();

            $newData = $wholesaler->fresh()->toArray();

            $oldStatus = $oldData['status'] == 1 ? 'Active' : 'Inactive';
            $newStatus = $newData['status'] == 1 ? 'Active' : 'Inactive';

            // AUDIT
            CustomFunctions::audit(
                module: 'Wholesalers',
                action: 'STATUS UPDATE',
                referenceId: $wholesaler->id,
                referenceTable: 'wholesalers',
                oldValues: $oldData,
                newValues: $newData,
                changedFields: ['status'],
                description: "Wholesaler '{$wholesaler->name}' status changed from {$oldStatus} to {$newStatus}."
            );

            DB::commit();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: Save /Update wholesaler.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 22 April 2026
     *
     * @updated-on: N/A
     */

    public function saveWholesalerAccounts(Request $request)
    {
        Log::info($request->all());

        $request->validate([
            'user_id' => 'required',
            'institution_id' => 'required',
            // 'wholesaler_accounts' => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            // 1. Get pharmacist_details.id based on user + institution
            $pharmacistIds = PharmacistDetails::where('user_id', $request->user_id)
                ->where('institution_id', $request->institution_id)
                ->pluck('id');

            if ($pharmacistIds->isEmpty()) {
                return response()->json([
                    'status' => 'E',
                    'message' => 'Pharmacist record not found',
                ]);
            }

            // 2. Delete old wholesaler mappings
            PharmacistWholesaler::whereIn('pharmacist_id', $pharmacistIds)
                ->delete();

            // 3. Insert new records
            foreach ($request->wholesaler_accounts as $wholesaler_id => $acc_no) {

                if (!empty($acc_no)) {
                    foreach ($pharmacistIds as $pharmacist_id) {

                        PharmacistWholesaler::create([
                            'pharmacist_id' => $pharmacist_id,
                            'wholesaler_id' => $wholesaler_id,
                            'acc_no' => $acc_no,
                            'created_by' => auth()->id(),
                            'updated_by' => auth()->id(),
                        ]);
                    }
                }
            }
            $mappedWholesalers = [];

            foreach ($request->wholesaler_accounts as $wholesaler_id => $acc_no) {
                if (!empty($acc_no)) {
                    $mappedWholesalers[] = [
                        'wholesaler_id' => $wholesaler_id,
                        'account_no' => $acc_no,
                    ];
                }
            }

            $wholesalerNames = \App\Models\Wholesalers::whereIn(
                'id',
                array_keys(array_filter($request->wholesaler_accounts))
            )->pluck('name')->toArray();

            $names = implode(', ', $wholesalerNames);

            CustomFunctions::audit(
                module: 'Wholesaler Accounts',
                action: 'ASSIGN',
                referenceId: $request->user_id,
                referenceTable: 'pharmacist_wholesalers',
                newValues: [
                    'user_id' => $request->user_id,
                    'institution_id' => $request->institution_id,
                    'wholesalers' => $mappedWholesalers,
                ],
                description: "Wholesaler accounts ({$names}) assigned to user ID {$request->user_id}."
            );

            DB::commit();

            return response()->json([
                'status' => 'S',
                'message' => 'Saved successfully',
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'E',
                'message' => 'Error saving data',
                'error_data' => $e->getMessage(),
            ]);
        }
    }
}
