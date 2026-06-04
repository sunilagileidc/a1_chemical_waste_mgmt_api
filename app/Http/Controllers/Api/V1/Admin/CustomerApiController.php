<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerApiController extends Controller
{
    /**
     * Fetch all customers
     */
    // public function index()
    // {
    //     try {

    //         // $customers = Customer::orderBy('id', 'DESC')->get();
    //         $customers = Customer::with('contacts')->get();

    //         return response()->json([
    //             'status' => 'S',
    //             'message' => trans('returnmessage.dataretreived'),
    //             'customers' => $customers
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

            $customers = Customer::with('individuals')->get();

            return response()->json([
                'status'    => 'S',
                'message'   => trans('returnmessage.dataretreived'),
                'customers' => $customers,
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
     * Fetch customer by slug
     */
    public function getCustomerBySlug($slug)
    {
        try {

            $customer = Customer::where('slug', $slug)->first();

            return response()->json([
                'status'   => 'S',
                'message'  => trans('returnmessage.dataretreived'),
                'customer' => $customer,
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
     * Fetch customer by id
     */
    public function getCustomerById($id)
    {
        try {

            $customer = Customer::find($id);

            return response()->json([
                'status'   => 'S',
                'message'  => trans('returnmessage.dataretreived'),
                'customer' => $customer,
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
     * Create / Update Customer
     */
    public function saveCustomer(Request $request)
    {
        $currenttime = date('Y-m-d H:i:s');

        $validator = Validator::make($request->all(), [
            'company_name'  => 'required|max:255',
            'company_email' => 'nullable|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {

            if ($request->id > 0) {

                Customer::where('id', $request->id)
                    ->update([
                        'company_name'      => $request->company_name,
                        'company_address'   => $request->company_address,
                        'company_postcode'  => $request->company_postcode,
                        'company_telephone' => $request->company_telephone,
                        'company_email'     => $request->company_email,
                        'hwr_code'          => $request->hwr_code,
                        'hwr_expiry_date'   => $request->hwr_expiry_date,
                        'sic_code'          => $request->sic_code,
                        'sic_desc'          => $request->sic_desc,
                        'active'            => $request->active,
                        'updated_by'        => auth()->id(),
                        'updated_at'        => $currenttime,
                    ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                ]);

            } else {

                $customer = Customer::create([
                    'company_name'      => $request->company_name,
                    'company_address'   => $request->company_address,
                    'company_postcode'  => $request->company_postcode,
                    'company_telephone' => $request->company_telephone,
                    'company_email'     => $request->company_email,
                    'hwr_code'          => $request->hwr_code,
                    'hwr_expiry_date'   => $request->hwr_expiry_date,
                    'sic_code'          => $request->sic_code,
                    'sic_desc'          => $request->sic_desc,
                    'active'            => $request->active ?? 1,
                    'slug'              => time(),
                    'created_by'        => auth()->id(),
                    'updated_by'        => auth()->id(),
                    'created_at'        => $currenttime,
                    'updated_at'        => $currenttime,
                ]);

                $customer->slug = $customer->id;
                $customer->save();

                return response()->json([
                    'status'   => 'S',
                    'message'  => trans('returnmessage.createdsuccessfully'),
                    'customer' => $customer,
                ]);
            }

        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);

        }
    }

    /**
     * Delete Customer
     */
    public function deleteCustomer($id)
    {
        try {

            Customer::where('id', $id)->delete();

            return response()->json([
                'status'  => 'S',
                'message' => trans('returnmessage.deletedsuccessfully'),
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
     * Update Customer Status
     */
    public function updateCustomerStatus(Request $request)
    {
        try {

            $customer = Customer::find($request->id);

            if ($customer->active == 1) {

                $customer->update([
                    'active'     => 0,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            } else {

                $customer->update([
                    'active'     => 1,
                    'updated_by' => auth()->id(),
                    'updated_at' => now(),
                ]);

            }

            return response()->json([
                'status'  => 'S',
                'message' => trans('returnmessage.saved_success'),
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
