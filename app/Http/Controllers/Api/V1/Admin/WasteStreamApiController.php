<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\WasteStream;
use Exception;
use Illuminate\Http\Request;
use Validator;

class WasteStreamApiController extends Controller
{

    public function index()
    {
        try {

            $streams = WasteStream::orderBy('id', 'desc')->get();

            return response()->json([
                'status'        => 'S',
                'message'       => 'Details retrieved successfully.',
                'waste_streams' => $streams,
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => 'Error processing the details.',
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function save(Request $request)
    {

        $validator = Validator::make($request->all(), [

            'waste_code'        => 'required',
            'waste_description' => 'required',
            'is_hazard'         => 'required|in:Y,N',

        ]);

        if ($validator->fails()) {

            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);

        }

        try {

            if ($request->id > 0) {

                $waste = WasteStream::find($request->id);

                $waste->update([

                    'waste_code'          => $request->waste_code,
                    'waste_description'   => $request->waste_description,
                    'is_hazard'           => $request->is_hazard,
                    'waste_components'    => $request->waste_components,
                    'waste_ewc'           => $request->waste_ewc,
                    'waste_color'         => $request->waste_color,
                    'waste_physical_form' => $request->waste_physical_form,
                    'waste_haz_code'      => $request->waste_haz_code,
                    'waste_risk_pharse'   => $request->waste_risk_pharse,
                    'waste_un_no'         => $request->waste_un_no,
                    'waste_pkg_grp'       => $request->waste_pkg_grp,
                    'waste_un_cls'        => $request->waste_un_cls,
                    'waste_ship_name'     => $request->waste_ship_name,
                    'waste_ass_raj'       => $request->waste_ass_raj,
                    'waste_rd_color'      => $request->waste_rd_color,
                    'updated_by'          => auth()->id(),
                    'updated_at'          => now(),
                ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => 'Updated successfully',
                ]);

            } else {

                WasteStream::create([

                    'waste_code'          => $request->waste_code,
                    'waste_description'   => $request->waste_description,
                    'is_hazard'           => $request->is_hazard,
                    'waste_components'    => $request->waste_components,
                    'waste_ewc'           => $request->waste_ewc,
                    'waste_color'         => $request->waste_color,
                    'waste_physical_form' => $request->waste_physical_form,
                    'waste_haz_code'      => $request->waste_haz_code,
                    'waste_risk_pharse'   => $request->waste_risk_pharse,
                    'waste_un_no'         => $request->waste_un_no,
                    'waste_pkg_grp'       => $request->waste_pkg_grp,
                    'waste_un_cls'        => $request->waste_un_cls,
                    'waste_ship_name'     => $request->waste_ship_name,
                    'waste_ass_raj'       => $request->waste_ass_raj,
                    'waste_rd_color'      => $request->waste_rd_color,

                    'slug'                => strtolower($request->waste_code)
                    . '-' . time(),

                    'created_by'          => auth()->id(),
                    'updated_by'          => auth()->id(),
                    'created_at'          => now(),
                    'updated_at'          => now(),

                ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => 'Created successfully',
                ]);

            }

        } catch (Exception $e) {

            return response()->json([

                'status'     => 'E',
                'message'    => 'Error processing the details.',
                'error_data' => $e->getMessage(),

            ]);

        }

    }

    public function bySlug($slug)
    {

        try {

            $waste = WasteStream::where('slug', $slug)->first();

            return response()->json([

                'status'       => 'S',
                'waste_stream' => $waste,

            ]);

        } catch (Exception $e) {

            return response()->json([

                'status'  => 'E',
                'message' => $e->getMessage(),

            ]);

        }

    }

}
