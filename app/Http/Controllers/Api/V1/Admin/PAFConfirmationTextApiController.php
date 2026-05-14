<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\PAFConfirmationText;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PAFConfirmationTextApiController extends Controller
{
    /**
     * @function: to fetch Non-Conformance details.
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
            $confirmation_texts = PAFConfirmationText::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'confirmation_texts' => $confirmation_texts]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store Confirmation Text details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 April 2026
     *
     * @updated-on: N/A
     */

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type'   => 'required|string|max:255',
            'note'   => 'required|string',
            'status' => 'required|in:1,0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {
            DB::beginTransaction();

            // ================= UPDATE =================
            if (! empty($request->id)) {

                $confirmation_text = PAFConfirmationText::findOrFail($request->id);

                // Check duplicate (excluding current ID)
                $exists = PAFConfirmationText::where('type', $request->type)
                    ->where('id', '!=', $request->id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'status'  => 'E',
                        'message' => 'Confirmation type already exists',
                    ]);
                }

                $confirmation_text->update([
                    'type'             => $request->type,
                    'drug_id'          => $request->drug_id,
                    'patient_category' => $request->patient_category,
                    'note'             => $request->note,
                    'status'           => $request->status,
                    'updated_by'       => Auth::id(),
                ]);

                $oldData = $confirmation_text->getOriginal();

                $newData = $confirmation_text->fresh()->toArray();

                CustomFunctions::audit(
                    module: 'PAF Confirmation Text',
                    action: 'UPDATE',
                    referenceId: $confirmation_text->id,
                    referenceTable: 'paf_confirmation_texts',
                    oldValues: $oldData,
                    newValues: $newData,
                    description: "Confirmation text '{$confirmation_text->type}' updated"
                );

                DB::commit();

                return response()->json([
                    'status'  => 'S',
                    'message' => 'Updated successfully',
                    'data'    => $confirmation_text,
                ]);
            }

            // ================= CREATE =================

            // Check duplicate
            $exists = PAFConfirmationText::where('type', $request->type)->exists();

            if ($exists) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Confirmation type already exists',
                ]);
            }

            $confirmation_text = PAFConfirmationText::create([
                'type'             => $request->type,
                'drug_id'          => $request->drug_id,
                'patient_category' => $request->patient_category,
                'note'             => $request->note,
                'status'           => $request->status,
                'created_by'       => Auth::id(),
            ]);

            CustomFunctions::audit(
                module: 'PAF Confirmation Text',
                action: 'CREATE',
                referenceId: $confirmation_text->id,
                referenceTable: 'paf_confirmation_texts',
                newValues: $confirmation_text->toArray(),
                description: "New confirmation text '{$confirmation_text->type}' created"
            );

            DB::commit();

            return response()->json([
                'status'  => 'S',
                'message' => 'Created successfully',
                'data'    => $confirmation_text,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status'     => 'E',
                'message'    => 'Error processing request',
                'error_data' => $e->getMessage(),
            ], 500);
        }
    }
    /**
     * @function: to Edit Confirmation text details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 April 2026
     *
     * @updated-on: N/A
     */

    public function editConfirmationText($id)
    {
        try {

            $confirmation_text = PAFConfirmationText::where('id', $id)
                ->firstOrFail();

            return response()->json([
                'status'            => 'S',
                'message'           => trans('returnmessage.data_return'),
                'confirmation_text' => $confirmation_text,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to Update the Confirmation Text status.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 27 April 2026
     *
     * @updated-on: N/A
     */
    public function updateConfirmationTextStatus(Request $request)
    {
        try {

            $confirmation_text = PAFConfirmationText::findOrFail($request->id);

            $oldStatus = $confirmation_text->status;

            // Toggle
            $confirmation_text->status = $oldStatus == 1 ? 0 : 1;
            $confirmation_text->save();

            $newStatus = $confirmation_text->status;

            // AUDIT
            CustomFunctions::audit(
                module: 'PAF Confirmation Text',
                action: 'STATUS UPDATE',
                referenceId: $confirmation_text->id,
                referenceTable: 'paf_confirmation_texts',
                oldValues: [
                    'status' => $oldStatus,
                ],
                newValues: [
                    'status' => $newStatus,
                ],
                description: "Confirmation text '{$confirmation_text->type}' status changed from " . ($oldStatus == 1 ? 'Active' : 'Inactive') . " to " . ($newStatus == 1 ? 'Active' : 'Inactive')
            );

            return response()->json([
                'status'  => 'S',
                'message' => trans('returnmessage.updatedsuccessfully'),
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to fetch confirmation text details.
     *
     * @author: Stalvin M
     *
     * @created-on: 07 May 2026
     *
     * @updated-on: N/A
     */

    public function fetchConfirmationTextByType(Request $request)
    {
        try {

            $types = $request->types ?? [];

            if (empty($types) || ! is_array($types)) {

                return response()->json([
                    'status'  => 'E',
                    'message' => 'Types array is required',
                ]);
            }

            $records = PAFConfirmationText::whereIn('type', $types)
                ->where('status', 1)
                ->get();

            $response = [];

            foreach ($types as $type) {

                $key = strtolower($type);

                $response[$key] = optional(
                    $records->firstWhere('type', $type)
                )->note;
            }

            return response()->json([
                'status'             => 'S',
                'message'            => 'Confirmation texts fetched successfully',
                'confirm_text_obj' => $response,
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
