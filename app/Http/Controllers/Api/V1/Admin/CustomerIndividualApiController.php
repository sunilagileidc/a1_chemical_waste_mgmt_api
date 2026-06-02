<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomerIndividual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerIndividualApiController extends Controller
{
    /**
     * Fetch all contacts
     */
    public function index()
    {
        try {
            $contacts = CustomerIndividual::with('customer')->get();

            return response()->json([
                'status'   => 'S',
                'message'  => trans('returnmessage.dataretreived'),
                'contacts' => $contacts,
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
     * Fetch contacts by customer
     */
    public function getContactsByCustomer($customer_id)
    {
        try {
            $contacts = CustomerIndividual::where(
                'customer_id',
                $customer_id
            )->get();

            return response()->json([
                'status'   => 'S',
                'message'  => trans('returnmessage.dataretreived'),
                'contacts' => $contacts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function getById($id)
    {
        try {

            $customerIndividual =
            CustomerIndividual::find($id);

            if (! $customerIndividual) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Contact not found',
                ]);
            }

            return response()->json([
                'status'              => 'S',
                'customer_individual' => $customerIndividual,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Fetch single contact
     */
    public function getContactById($id)
    {
        try {
            $contact = CustomerIndividual::find($id);

            return response()->json([
                'status'  => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'contact' => $contact,
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
     * Save / Update Contact
     */
    public function saveContact(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required',
            'name'        => 'required',
            'telephone'   => 'required',
            'email'       => 'required',
            'position'    => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {

            if ($request->id > 0) {

                CustomerIndividual::where('id', $request->id)
                    ->update([
                        'customer_id' => $request->customer_id,
                        'name'        => $request->name,
                        'telephone'   => $request->telephone,
                        'email'       => $request->email,
                        'position'    => $request->position,
                        'active'      => $request->active ?? 1,
                    ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                ]);

            } else {

                CustomerIndividual::create([
                    'customer_id' => $request->customer_id,
                    'name'        => $request->name,
                    'telephone'   => $request->telephone,
                    'email'       => $request->email,
                    'position'    => $request->position,
                    'active'      => $request->active ?? 1,
                ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => trans('returnmessage.createdsuccessfully'),
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
     * Delete Contact
     */
    public function deleteContact($id)
    {
        try {

            CustomerIndividual::where('id', $id)->delete();

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
     * Update Status
     */
    public function updateContactStatus(Request $request)
    {
        try {

            $contact = CustomerIndividual::find($request->id);

            $status = $contact->active == 1 ? 0 : 1;

            $contact->update([
                'active' => $status,
            ]);

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
