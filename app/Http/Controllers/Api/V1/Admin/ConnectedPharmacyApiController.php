<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\ConnectedPharmacies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ConnectedPharmacyApiController extends Controller
{
    /**
     * @function: to fetch institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 08 March, 2026
     *
     * @updated-on: N/A
     */
    public function getConnectedOutpatient(Request $request)
    {
        try {

            $con_pharmacies = ConnectedPharmacies::with('pharmacy')
                ->where('user_id', $request->user_id)
                ->where('institution_id', $request->institution_id)
                ->whereHas('pharmacy', function ($q) {
                    $q->where('institution_type', 'Outpatient Pharmacy');
                })
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($item) {

                    $pharmacy = $item->pharmacy[0] ?? null;

                    return [
                        'id' => $item->id,
                        'connected_pharmacy_id' => $item->connected_pharmacy_id,
                        'user_id' => $item->user_id,
                        'institution_id' => $item->institution_id,
                        'status' => $item->status,

                        // merged pharmacy fields
                        'name' => $pharmacy->name ?? null,
                        'address' => $pharmacy->address ?? null,
                        'post_code' => $pharmacy->post_code ?? null,
                        'pharmacy_id' => $pharmacy->id ?? null,
                    ];
                });

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.already_exists'),
                'con_pharmacies' => $con_pharmacies,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'errordata' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to fetch institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 08 March, 2026
     *
     * @updated-on: N/A
     */
    public function getConnectedHomecare(Request $request)
    {
        try {

            $con_homecares = ConnectedPharmacies::with('pharmacy')
                ->where('user_id', $request->user_id)
                ->where('institution_id', $request->institution_id)
                ->whereHas('pharmacy', function ($q) {
                    $q->where('institution_type', 'Homecare');
                })
                ->orderBy('updated_at', 'desc')
                ->get()
                ->map(function ($item) {

                    $pharmacy = $item->pharmacy[0] ?? null;

                    return [
                        'id' => $item->id,
                        'connected_pharmacy_id' => $item->connected_pharmacy_id,
                        'user_id' => $item->user_id,
                        'institution_id' => $item->institution_id,
                        'status' => $item->status,

                        //  merged pharmacy fields
                        'name' => $pharmacy->name ?? null,
                        'address' => $pharmacy->address ?? null,
                        'post_code' => $pharmacy->post_code ?? null,
                        'pharmacy_id' => $pharmacy->id ?? null,
                    ];
                });

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.already_exists'),
                'con_homecares' => $con_homecares,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'errordata' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to store institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {

        try {
            DB::beginTransaction();

            if (ConnectedPharmacies::where('connected_pharmacy_id', $request->connected_pharmacy_id)
                ->where('user_id', $request->user_id)
                ->where('institution_id', $request->institution_id)->count() > 0) {
                return response()->json([
                    'status' => 'E',
                    'message' => trans('returnmessage.already_exists'),
                ]);
            }

            //  Create record

            $con_pharmacies = ConnectedPharmacies::create($request->all());

            CustomFunctions::audit(
                module: 'Institutions',
                action: 'CREATE',
                referenceId: $con_pharmacies->id,
                referenceTable: 'institutions',
                newValues: $con_pharmacies->toArray(),
                description: 'Connected pharmacy created'
            );
            DB::commit();
            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.createdsuccessfully'),
                'con_pharmacies' => $con_pharmacies,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'errordata' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to update Lookup details.
     *
     * @author: Raghavendra kumar S
     *
     * @created-on: 20 March, 2026
     *
     * @updated-on: N/A
     */
    public function updateConnPharmacyStatus(request $request)
    {
        try {
            $conn_pharmacies = ConnectedPharmacies::where('id', $request->id)->first();
            if ($conn_pharmacies->status == 1) {
                $status = ConnectedPharmacies::where('id', $request->id)->update(['status' => 0]);
            } else {
                $status = ConnectedPharmacies::where('id', $request->id)->update(['status' => 1]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
