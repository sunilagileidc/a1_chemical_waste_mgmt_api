<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\Indications;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IndicationsApiController extends Controller
{
    /**
     * @function: to fetch Indications details.
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
            $indications = Indications::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'indications' => $indications]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch Indications details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function fetchIndications()
    {
        try {
            $indications = Indications::where('status', 1)->orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'indications' => $indications]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update Indications status.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function updateIndicationStatus(request $request)
    {
        try {
            DB::beginTransaction();
            $indications = Indications::findOrFail($request->id);

            $oldData = $indications->toArray();

            // Toggle status
            $indications->status = $indications->status == 1 ? 0 : 1;
            $indications->save();

            $newData = $indications->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Indications',
                action: 'STATUS UPDATE',
                referenceId: $indications->id,
                referenceTable: 'indications',
                oldValues: $oldData,
                newValues: $newData,
                description: "Indication '{$indications->name}' status changed from " . ($oldData['status'] == 1 ? 'Active' : 'Inactive') . " to " . ($newData['status'] == 1 ? 'Active' : 'Inactive')
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit Indications details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 Mar 2026
     *
     * @updated-on: N/A
     */
    public function editIndications($slug)
    {
        try {
            $indications = Indications::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'indications' => $indications]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store Indications details.
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
            'name'        => 'required',
            'sequence'    => 'required',
            'description' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            DB::beginTransaction();
            $authUserId = Auth::id();

            if ($request->name) {
                $exists = Indications::where('id', '!=', $request->id)->where('name', $request->name)->first();
                if ($exists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.already_exists')]);
                }
            }

            if ($id > 0) {
                $indication = Indications::findOrFail($id);

                $oldData = $indication->toArray();

                $indication = Indications::where('id', $id)
                    ->update([
                        'name'        => $request->name,
                        'description' => $request->description,
                        'sequence'    => $request->sequence,
                        'status'      => $request->status,
                        'updated_by'  => $authUserId,
                    ]);
                $indication = Indications::where('id', $request->id)->first();

                $newData = $indication->fresh()->toArray();

                CustomFunctions::audit(
                    module: 'Indications',
                    action: 'UPDATE',
                    referenceId: $indication->id,
                    referenceTable: 'indications',
                    oldValues: $oldData,
                    newValues: $newData,
                    changedFields: array_keys($indication->getChanges()),
                    description: "Indication '{$indication->name}' updated. " . "Status: " . ($oldData['status'] == 1 ? 'Active' : 'Inactive') . " → " . ($newData['status'] == 1 ? 'Active' : 'Inactive')
                );
                DB::commit();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'indication' => $indication]);
            } else {
                $indication = Indications::create([
                    'name'        => $request->name,
                    'description' => $request->description,
                    'sequence'    => $request->sequence,
                    'status'      => $request->status,
                    'created_by'  => $authUserId,
                    'updated_by'  => $authUserId,
                ]);
                CustomFunctions::audit(
                    module: 'Indications',
                    action: 'CREATE',
                    referenceId: $indication->id,
                    referenceTable: 'indications',
                    newValues: $indication->toArray(),
                    description: "New indication '{$indication->name}' created"
                );
                DB::commit();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'indication' => $indication]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete Indications details.
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

            $deleteindication = Indications::findOrFail($id);

            $oldData = $deleteindication->toArray();

            $deleteindication->delete();

            CustomFunctions::audit(
                module: 'Indications',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'indications',
                oldValues: $oldData,
                description: "Indication '{$oldData['name']}' deleted"
            );
            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

}
