<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\MarketingHolders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MarketingHoldersApiController extends Controller
{
    /**
     * @function: to fetch MarketingHolders details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $marketing_holders = MarketingHolders::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'marketing_holders' => $marketing_holders]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to fetch MarketingHolders details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function fetchMarketingHolders()
    {
        try {
            $marketing_holders = MarketingHolders::where('status', 1)->orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'marketing_holders' => $marketing_holders]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update MarketingHolders status.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function updateMarketingHolderStatus(request $request)
    {
        try {
            DB::beginTransaction();
            $marketing_holders = MarketingHolders::findOrFail($request->id);

            $oldData = $marketing_holders->toArray();

            // Toggle status
            $marketing_holders->status = $marketing_holders->status == 1 ? 0 : 1;
            $marketing_holders->save();

            $newData = $marketing_holders->fresh()->toArray();

            $statusTextOld = $oldData['status'] == 1 ? 'Active' : 'Inactive';
            $statusTextNew = $newData['status'] == 1 ? 'Active' : 'Inactive';

            CustomFunctions::audit(
                module: 'Marketing Holders',
                action: 'STATUS UPDATE',
                referenceId: $marketing_holders->id,
                referenceTable: 'marketing_holders',
                oldValues: $oldData,
                newValues: $newData,
                changedFields: ['status'],
                description: "Marketing Holder '{$marketing_holders->name}' status changed from {$statusTextOld} to {$statusTextNew}."
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit MarketingHolders details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function editMarketingHolder($slug)
    {
        try {
            $marketing_holders = MarketingHolders::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'marketing_holders' => $marketing_holders]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store MarketingHolders details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        $id        = $request->id;
        $validator = Validator::make($request->all(), [
            'contact_name'  => 'required',
            'contact_email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            DB::beginTransaction();
            $authUserId = Auth::id();

            if ($request->name) {
                $exists = MarketingHolders::where('id', '!=', $request->id)->where('contact_name', $request->contact_name)->first();
                if ($exists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.already_exists')]);
                }
            }

            if ($id > 0) {
                $marketing_holders = MarketingHolders::findOrFail($id);

                $oldData = $marketing_holders->toArray();

                $marketing_holders = MarketingHolders::where('id', $id)
                    ->update([
                        'contact_name'  => $request->contact_name,
                        'contact_email' => $request->contact_email,
                        'logo'          => $request->logo,
                        'status'        => $request->status,
                        'description'   => $request->description,
                        'updated_by'    => $authUserId,
                    ]);

                $marketing_holders = MarketingHolders::where('id', $request->id)->first();
                $newData           = $marketing_holders->fresh()->toArray();
                CustomFunctions::audit(
                    module: 'Marketing Holders',
                    action: 'UPDATE',
                    referenceId: $marketing_holders->id,
                    referenceTable: 'marketing_holders',
                    oldValues: $oldData,
                    newValues: $newData,
                    changedFields: array_keys($marketing_holders->getChanges()),
                    description: "Marketing Holder '{$marketing_holders->contact_name}' details updated."
                );
                DB::commit();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'marketing_holders' => $marketing_holders]);
            } else {
                $marketing_holders = MarketingHolders::create([
                    'contact_name'  => $request->contact_name,
                    'contact_email' => $request->contact_email,
                    'logo'          => $request->logo,
                    'status'        => $request->status,
                    'description'   => $request->description,
                    'created_by'    => $authUserId,
                    'updated_by'    => $authUserId,
                ]);
                CustomFunctions::audit(
                    module: 'Marketing Holders',
                    action: 'CREATE',
                    referenceId: $marketing_holders->id,
                    referenceTable: 'marketing_holders',
                    newValues: $marketing_holders->toArray(),
                    description: "New Marketing Holder '{$marketing_holders->contact_name}' created with email '{$marketing_holders->contact_email}'."
                );
                DB::commit();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'marketing_holders' => $marketing_holders]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete MarketingHolders details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $marketing_holders = MarketingHolders::findOrFail($id);

            $oldData = $marketing_holders->toArray();

            $marketing_holders->delete();

            CustomFunctions::audit(
                module: 'Marketing Holders',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'marketing_holders',
                oldValues: $oldData,
                description: "Marketing Holder '{$oldData['contact_name']}' deleted."
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch Marketing Holders details using drug id.
     *
     * @author: Santhosha G
     *
     * @created-on: 18 Mar 2026
     *
     * @updated-on: N/A
     */
    public function fetchMarketingHoldersById(Request $request)
    {
        try {

            $drugid = $request->drug_id;

            $marketing_holders = MarketingHolders::where('status', 1)
                ->whereHas('drugs', function ($q) use ($drugid) {
                    $q->where('drugs.id', $drugid);
                })
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status'            => 'S',
                'message'           => trans('returnmessage.dataretreived'),
                'marketing_holders' => $marketing_holders,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }
}
