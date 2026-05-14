<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\Pharmacies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PharmacyApiController extends Controller
{
    /**
     * @function: to fetch pharmacies details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 5 March, 2026
     *
     * @updated-on: N/A
     */
    public function index(Request $request)
    {
        try {
            $pharmacies = Pharmacies::orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'pharmacies' => $pharmacies]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store pharmacies details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 5 March, 2026
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {

        try {
            DB::beginTransaction();

            if (Pharmacies::where('name', $request->name)->count() > 0) {
                return response()->json([
                    'status'  => 'E',
                    'message' => trans('returnmessage.menu') . ' ' .
                    $request->name . ', ' .
                    trans('returnmessage.already_exists'),
                ]);
            }

            // Create record
            $pharmacy = Pharmacies::create($request->all());

            $description = "New pharmacy '{$pharmacy->name}' has been created. " .
                "Type: " . ($pharmacy->institution_type ?? 'N/A') . ", " .
                "Location: " . ($pharmacy->city ?? 'N/A') . ", " . ($pharmacy->country ?? 'N/A') . ". ";

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'CREATE',
                referenceId: $pharmacy->id,
                referenceTable: 'pharmacies',
                newValues: $pharmacy->toArray(),
                description: $description
            );
            DB::commit();
            return response()->json([
                'status'   => 'S',
                'message'  => trans('returnmessage.createdsuccessfully'),
                'pharmacy' => $pharmacy,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'    => 'E',
                'message'   => trans('returnmessage.error_processing'),
                'errordata' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to edit pharmacy details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 March, 2026
     *
     * @updated-on: N/A
     */
    public function edit($slug)
    {
        try {
            $pharmacy = Pharmacies::where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'pharmacy' => $pharmacy]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $pharmacy = Pharmacies::findOrFail($id);

            $oldData = $pharmacy->toArray();

            $pharmacy->update($request->all());

            $newData = $pharmacy->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'UPDATE',
                referenceId: $pharmacy->id,
                referenceTable: 'pharmacies',
                oldValues: $oldData,
                newValues: $newData,
                changedFields: array_keys($pharmacy->getChanges()),
                description: "Pharmacy '{$pharmacy->name}' details updated"
            );
            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'pharmacy' => $pharmacy]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }
    /**
     * @function: to delete institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try
        {
            DB::beginTransaction();

            $pharmacy = Pharmacies::findOrFail($id);

            $oldData = $pharmacy->toArray();

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'pharmacies',
                oldValues: $oldData,
                description: "Pharmacy '{$pharmacy->name}' deleted"
            );

            $pharmacy->delete();

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_delete')]);
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
    public function updatePharmacyStatus(request $request)
    {
        try {
            DB::beginTransaction();
            $pharmacy = Pharmacies::findOrFail($request->id);

            $oldData = $pharmacy->toArray();

            // Toggle status
            $pharmacy->status = $pharmacy->status == 1 ? 0 : 1;
            $pharmacy->save();

            $newData = $pharmacy->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'STATUS UPDATE',
                referenceId: $pharmacy->id,
                referenceTable: 'pharmacies',
                oldValues: $oldData,
                newValues: $newData,
                description: "pharmacy '$pharmacy->name' status update"
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to fetch active pharmacies
     *
     * @author: S Raghavendra kumar
     *
     * @created-on: 06 March, 2026
     *
     * @updated-on: N/A
     */
    public function fetchActivePharmacies()
    {
        try {
            $pharmacies = Pharmacies::orderBy('id', 'desc')->where('status', 1)->get(['id', 'name', 'type', 'address', 'postcode', 'id as pharmacy_id']);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'pharmacies' => $pharmacies]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

}
