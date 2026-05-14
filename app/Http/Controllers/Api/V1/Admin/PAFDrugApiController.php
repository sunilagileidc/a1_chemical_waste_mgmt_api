<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\PAFDrug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PAFDrugApiController extends Controller
{
    /**
     * @function: to fetch drug details.
     *
     * @author: Santhosha G
     *
     * @created-on: 16 Feb 2026
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $drugs = PAFDrug::orderBy("id", "desc")->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'drug' => $drugs]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit drug details.
     *
     * @author: Santhosha G
     *
     * @created-on: 16 Feb 2026
     *
     * @updated-on: N/A
     */
    public function editDrug($slug)
    {
        try {
            $drug = PAFDrug::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'drug' => $drug]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store drug details.
     *
     * @author: Santhosha G
     *
     * @created-on: 16 Feb 2026
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        $currenttime = date('Y-m-d h:i:s');
        $id          = $request->id;
        $validator   = Validator::make($request->all(), [
            'drug_name'         => 'required',
            'capsule_strength'  => 'required',
            'capsules_per_cyle' => 'required',
            'number_of_cycles'  => 'required',
            'total_capsules'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($request->drug_name) {
                $exists = PAFDrug::where('id', '!=', $request->id)->where('drug_name', $request->drug_name)->first();
                if ($exists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.already_exists')]);
                }
            }

            if ($id > 0) {
                $drug = PAFDrug::where('id', $id)
                    ->update([
                        'drug_name'         => $request->drug_name,
                        'capsule_strength'  => $request->capsule_strength,
                        'capsules_per_cyle' => $request->capsules_per_cyle,
                        'number_of_cycles'  => $request->number_of_cycles,
                        'total_capsules'    => $request->total_capsules,
                        'updated_at'        => $currenttime,
                    ]);
                $drug = PAFDrug::where('id', $request->id)->first();
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'drug' => $drug]);
            } else {
                $drug = PAFDrug::create([
                    'drug_name'         => $request->drug_name,
                    'capsule_strength'  => $request->capsule_strength,
                    'capsules_per_cyle' => $request->capsules_per_cyle,
                    'number_of_cycles'  => $request->number_of_cycles,
                    'total_capsules'    => $request->total_capsules,
                    'created_at'        => $currenttime,
                    'updated_at'        => $currenttime,
                ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'drug' => $drug]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete drug details.
     *
     * @author: Santhosha G
     *
     * @created-on: 16 Feb 2026
     *
     * @updated-on: N/A
     */
    public function deleteDrug($id)
    {
        try {
            $deleteDrug = PAFDrug::where('id', $id)->delete();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
