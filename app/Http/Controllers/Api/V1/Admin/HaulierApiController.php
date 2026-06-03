<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Haulier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HaulierApiController extends Controller
{
    /**
     * Fetch all hauliers
     */
    // public function index()
    // {
    //     try {

    //         $hauliers = Haulier::orderBy('id', 'desc')->get();

    //         return response()->json([
    //             'status' => 'S',
    //             'message' => trans('returnmessage.dataretreived'),
    //             'hauliers' => $hauliers
    //         ]);

    //     } catch (\Exception $e) {

    //         return response()->json([
    //             'status' => 'E',
    //             'message' => trans('returnmessage.error_processing'),
    //             'error_data' => $e->getMessage()
    //         ]);
    //     }
    // }
    public function index()
    {
        try {

            $hauliers = Haulier::with('individuals')->get();

            return response()->json([
                'status'    => 'S',
                'message'   => trans('returnmessage.dataretreived'),
                'hauliers' => $hauliers,
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
     * Fetch haulier by ID
     */
    public function getHaulierById($id)
    {
        try {

            $haulier = Haulier::find($id);

            return response()->json([
                'status' => 'S',
                'haulier' => $haulier
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fetch haulier by slug
     */
    public function getHaulierBySlug($slug)
    {
        try {

            $haulier = Haulier::where('slug', $slug)->first();

            return response()->json([
                'status' => 'S',
                'haulier' => $haulier
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create / Update haulier
     */
    public function saveHaulier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'haulier_name' => 'required',
            'haulier_email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E',
                'message' => $validator->errors()->all()
            ]);
        }

        try {

            if ($request->id > 0) {

                Haulier::where('id', $request->id)->update([
                    'haulier_name' => $request->haulier_name,
                    'haulier_address' => $request->haulier_address,
                    'haulier_postcode' => $request->haulier_postcode,
                    'haulier_telephone' => $request->haulier_telephone,
                    'haulier_email' => $request->haulier_email,
                    'haulier_license' => $request->haulier_license,
                    'active' => $request->active,
                ]);

                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully')
                ]);
            }

            Haulier::create([
                'haulier_name' => $request->haulier_name,
                'haulier_address' => $request->haulier_address,
                'haulier_postcode' => $request->haulier_postcode,
                'haulier_telephone' => $request->haulier_telephone,
                'haulier_email' => $request->haulier_email,
                'haulier_license' => $request->haulier_license,
                'active' => 1,
                'slug' => uniqid(),
            ]);

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.createdsuccessfully')
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage()
            ]);
        }
    }

    /**
     * Delete haulier
     */
    public function deleteHaulier($id)
    {
        try {

            Haulier::where('id', $id)->delete();

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.deletedsuccessfully')
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update haulier status
     */
    public function updateHaulierStatus(Request $request)
    {
        try {

            $haulier = Haulier::find($request->id);

            $haulier->active = $haulier->active ? 0 : 1;

            $haulier->save();

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.saved_success')
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage()
            ]);
        }
    }
}