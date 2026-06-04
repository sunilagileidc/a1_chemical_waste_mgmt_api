<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\HaulierIndividual;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HaulierIndividualApiController extends Controller
{
    /**
     * Fetch all contacts
     */
    public function index()
    {
        try {
            $contacts = HaulierIndividual::with('haulier')->get();

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
     * Fetch contacts by haulier
     */
    public function getContactsByHaulier($haulier_id)
    {
        try {
            $contacts = HaulierIndividual::where(
                'haulier_id',
                $haulier_id
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

            $haulierIndividual =
            HaulierIndividual::find($id);

            if (! $haulierIndividual) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Contact not found',
                ]);
            }

            return response()->json([
                'status'             => 'S',
                'haulier_individual' => $haulierIndividual,
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
            $contact = HaulierIndividual::find($id);

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
            'haulier_id' => 'required',
            'name'       => 'required',
            'telephone'  => 'required',
            'email'      => 'required',
            'position'   => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {

            if ($request->id > 0) {

                HaulierIndividual::where('id', $request->id)
                    ->update([
                        'haulier_id' => $request->haulier_id,
                        'name'       => $request->name,
                        'telephone'  => $request->telephone,
                        'email'      => $request->email,
                        'position'   => $request->position,
                        'active'     => $request->active ?? 1,
                        'updated_by' => auth()->id(),
                        'updated_at' => now(),
                    ]);

                return response()->json([
                    'status'  => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                ]);

            } else {

                HaulierIndividual::create([
                    'haulier_id' => $request->haulier_id,
                    'name'       => $request->name,
                    'telephone'  => $request->telephone,
                    'email'      => $request->email,
                    'position'   => $request->position,
                    'active'     => $request->active ?? 1,
                    'created_by' => auth()->id(),
                    'updated_by' => auth()->id(),
                    'created_at' => now(),
                    'updated_at' => now(),
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

            HaulierIndividual::where('id', $id)->delete();

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

            $contact = HaulierIndividual::find($request->id);

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
