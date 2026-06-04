<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupplierIndividual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SupplierIndividualApiController extends Controller
{
    /**
     * Fetch all contacts
     */
    public function index()
    {
        try {
            $contacts = SupplierIndividual::with('supplier')->get();

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
     * Fetch contacts by supplier
     */
    public function getContactsBySupplier($supplier_id)
    {
        try {
            $contacts = SupplierIndividual::where(
                'supplier_id',
                $supplier_id
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

            $supplierIndividual =
            SupplierIndividual::find($id);

            if (! $supplierIndividual) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Contact not found',
                ]);
            }

            return response()->json([
                'status'              => 'S',
                'supplier_individual' => $supplierIndividual,
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
            $contact = SupplierIndividual::find($id);

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
            'supplier_id' => 'required',
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

                SupplierIndividual::where('id', $request->id)
                    ->update([
                        'supplier_id' => $request->supplier_id,
                        'name'        => $request->name,
                        'telephone'   => $request->telephone,
                        'email'       => $request->email,
                        'position'    => $request->position,
                        'active'      => $request->active ?? 1,
                        'updated_by'  => auth()->id(),
                        'updated_at'  => now(),
                    ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                ]);

            } else {

                SupplierIndividual::create([
                    'supplier_id' => $request->supplier_id,
                    'name'        => $request->name,
                    'telephone'   => $request->telephone,
                    'email'       => $request->email,
                    'position'    => $request->position,
                    'active'      => $request->active ?? 1,
                    'created_by'  => auth()->id(),
                    'updated_by'  => auth()->id(),
                    'created_at'  => now(),
                    'updated_at'  => now(),
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

            SupplierIndividual::where('id', $id)->delete();

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

            $contact = SupplierIndividual::find($request->id);

            $status = $contact->active == 1 ? 0 : 1;

            $contact->update([
                'active'     => $status,
                'updated_by' => auth()->id(),
                'updated_at' => now(),
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
