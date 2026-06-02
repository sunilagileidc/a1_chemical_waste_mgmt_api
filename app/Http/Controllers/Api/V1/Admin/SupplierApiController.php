<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierApiController extends Controller
{
    /**
     * Fetch all suppliers
     */
    // public function index()
    // {
    //     try {
    //         $suppliers = Supplier::orderBy('id', 'desc')->get();

    //         return response()->json([
    //             'status' => 'S',
    //             'message' => trans('returnmessage.dataretreived'),
    //             'suppliers' => $suppliers
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

            $suppliers = Supplier::with('individuals')->get();

            return response()->json([
                'status'    => 'S',
                'message'   => trans('returnmessage.dataretreived'),
                'suppliers' => $suppliers,
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
     * Fetch supplier by ID
     */
    public function getSupplierById($id)
    {
        try {
            $supplier = Supplier::find($id);

            return response()->json([
                'status' => 'S',
                'supplier' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Fetch supplier by slug
     */
    public function getSupplierBySlug($slug)
    {
        try {
            $supplier = Supplier::where('slug', $slug)->first();

            return response()->json([
                'status' => 'S',
                'supplier' => $supplier
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create / Update supplier
     */
    public function saveSupplier(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required',
            'supplier_email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'E',
                'message' => $validator->errors()->all()
            ]);
        }

        try {

            if ($request->id > 0) {

                Supplier::where('id', $request->id)->update([
                    'supplier_name' => $request->supplier_name,
                    'supplier_address' => $request->supplier_address,
                    'supplier_postcode' => $request->supplier_postcode,
                    'supplier_telephone' => $request->supplier_telephone,
                    'supplier_email' => $request->supplier_email,
                    'supplier_license' => $request->supplier_license,
                    'active' => $request->active,
                ]);

                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully')
                ]);
            }

            Supplier::create([
                'supplier_name' => $request->supplier_name,
                'supplier_address' => $request->supplier_address,
                'supplier_postcode' => $request->supplier_postcode,
                'supplier_telephone' => $request->supplier_telephone,
                'supplier_email' => $request->supplier_email,
                'supplier_license' => $request->supplier_license,
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
     * Delete supplier
     */
    public function deleteSupplier($id)
    {
        try {

            Supplier::where('id', $id)->delete();

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
     * Update supplier status
     */
    public function updateSupplierStatus(Request $request)
    {
        try {

            $supplier = Supplier::find($request->id);

            $supplier->active = $supplier->active ? 0 : 1;

            $supplier->save();

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