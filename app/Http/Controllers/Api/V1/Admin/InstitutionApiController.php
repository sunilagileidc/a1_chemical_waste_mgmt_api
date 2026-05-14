<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\ConnectedPharmacies;
use App\Models\InstitutionContacts;
use App\Models\Institutions;
use App\Models\PharmacistDetails;
use App\Models\PrescriberDetails;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstitutionApiController extends Controller
{
    /**
     * @function: to fetch institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function index(Request $request)
    {
        try {
            $institutions = Institutions::with('institution_contacts')->orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'institutions' => $institutions]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
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

            if (Institutions::where('name', $request->name)->count() > 0) {
                return response()->json([
                    'status' => 'E',
                    'message' => trans('returnmessage.menu') . ' ' .
                    $request->name . ', ' .
                    trans('returnmessage.already_exists'),
                ]);
            }

            // Generate ref_number (First 3 letters + random 4 digits)
            $prefix = strtoupper(substr(preg_replace('/\s+/', '', $request->name), 0, 3));
            $number = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            $request['ref_number'] = $prefix . $number;
            $request['created_by'] = auth()->id();

            // Create record

            $institution = Institutions::create($request->all());
            // If form = Pharmacy → store id in pharmacy_id
            if ($request->form == 'Pharmacy') {
                $institution->pharmacy_id = $institution->id;
                $institution->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Save Institution Contacts
            |--------------------------------------------------------------------------
             */
            if ($request->has('selected')) {

                foreach ($request->selected as $contact) {

                    InstitutionContacts::create([
                        'institution_id' => $institution->id,
                        'user_id' => $contact['id'],
                        'email' => $contact['email'],
                        'name' => $contact['name'] . ' ' . $contact['lastname'],
                        'status' => 1,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'CREATE',
                referenceId: $institution->id,
                referenceTable: 'institutions',
                newValues: $institution->toArray(),
                description: "New pharmacy '$institution->name' created"
            );
            DB::commit();
            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.createdsuccessfully'),
                'institution' => $institution,
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
     * @function: to edit institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function edit($slug)
    {
        try {
            $institution = Institutions::where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'institution' => $institution]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $id)
    {

        try {
            DB::beginTransaction();

            // if (Institutions::where('name', $request->name)->where('id', '!=', $id)->count() > 0) {
            //     return response()->json(['status' => 'E', 'message' => trans('returnmessage.menu') . $request->title . trans('returnmessage.already_exists')]);
            // }
            $institution = Institutions::findOrFail($id);

            $oldData = $institution->toArray();

            $institution->update($request->all());
            /*
            |--------------------------------------------------------------------------
            | Save Institution Contacts
            |--------------------------------------------------------------------------
             */
            if ($request->has('selected')) {

                // Remove old contacts for this institution
                InstitutionContacts::where('institution_id', $institution->id)->delete();

                foreach ($request->selected as $contact) {

                    InstitutionContacts::create([
                        'institution_id' => $institution->id,
                        'user_id' => $contact['id'],
                        'email' => $contact['email'],
                        'name' => $contact['name'] . ' ' . $contact['lastname'],
                        'status' => 1,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
            $newData = $institution->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'UPDATE',
                referenceId: $institution->id,
                referenceTable: 'institutions',
                oldValues: $oldData,
                newValues: $newData,
                changedFields: array_keys($institution->getChanges()),
                description: "Pharmacy '$institution->name' updated"
            );
            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'institution' => $institution]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 6 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try
        {
            DB::beginTransaction();

            $institution = Institutions::findOrFail($id);

            $oldData = $institution->toArray();

            $institution->delete();

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'institutions',
                oldValues: $oldData,
                description: "Pharmacy '$institution->name' deleted"
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_delete')]);
        }
    }

    /**
     * @function: to fetch active institutions
     *
     * @author: Stalvin M
     *
     * @created-on: 17 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function fetchActiveInstitutions(Request $request)
    {
        try {

            $query = Institutions::where('status', 1);

            // Apply filter only if type is provided
            if ($request->type) {
                $query->where('institution_type', $request->type);
            }

            $institutions = $query
                ->orderBy('id', 'desc')
                ->get([
                    'id',
                    'id as institution_id',
                    'name',
                    'institution_type',
                    'address',
                    'delivery_address',
                    'delivery_post_code',
                    'ordering_address',
                    'ordering_post_code',
                ]);

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'institutions' => $institutions,
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
     * @function: to fetch institution details based on type.
     *
     * @author: Santhosha G
     *
     * @created-on: 18 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function fetchInstitutionByType(Request $request)
    {
        try {
            $institutions = Institutions::where('institution_type', $request->type)->where('status', 1)->orderBy('updated_at', 'desc')->get();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'institutions' => $institutions]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to Update the institution status.
     *
     * @author: Santhosha G
     *
     * @created-on: 23 Feb 2026
     *
     * @updated-on: N/A
     */
    public function updateInstitutionStatus(request $request)
    {
        try {
            DB::beginTransaction();
            $institution = Institutions::findOrFail($request->id);

            $oldData = $institution->toArray();
            //Is linked is commented as of now
            // $isLinked =
            // InstitutionContacts::where('institution_id', $institution->id)->exists() ||
            // ConnectedPharmacies::where('pharmacy_id', $institution->id)->exists() ||
            // PharmacistDetails::where('institution_id', $institution->id)->exists() ||
            // PrescriberDetails::where('institution_id', $institution->id)->exists();

            // if ($isLinked) {
            //     return response()->json(['status' => 'E', 'message' => 'This pharmacy is linked with other records and cannot be inactivated.']);

            // }
            //Is linked is commented as of now

            // Toggle status
            $institution->status = $institution->status == 1 ? 0 : 1;
            $institution->save();

            $newData = $institution->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Pharmacies',
                action: 'STATUS UPDATE',
                referenceId: $institution->id,
                referenceTable: 'institutions',
                oldValues: $oldData,
                newValues: $newData,
                description: "Pharmacy '$institution->name' status update"
            );

            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to fetch institution details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 07 Mar, 2026
     *
     * @updated-on: N/A
     */
    public function fetchActivePharmacies(Request $request)
    {
        try {
            $pharmacies = Institutions::where("status", 1)->orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'pharmacies' => $pharmacies]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch institution contact details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 09 March, 2026
     *
     * @updated-on: N/A
     */
    /**
     * @function: to fetch institution contact details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 09 March, 2026
     *
     * @updated-on: N/A
     */
    public function fetchInstitutionContacts(Request $request)
    {
        try {
            $inst_contacts = InstitutionContacts::with('contact_user:id,name,lastname,email')
                ->where('institution_id', $request->institution_id)
                ->get()
                ->map(function ($contact) {
                    return [
                        'id' => $contact->contact_user->id, // actual user ID
                        'name' => $contact->contact_user->name,
                        'lastname' => $contact->contact_user->lastname,
                        'email' => $contact->contact_user->email,
                    ];
                });

            // $inst_contacts = InstitutionContacts::with('contact_user:name,lastname')->where(
            //     'institution_id',
            //     $request->institution_id
            // )
            //     ->get(['user_id as id', 'name', 'email']);

            // If no contacts exist for this pharmacy, load default lead pharmacist
            // if ($inst_contacts->isEmpty()) {

            //     $inst_contacts = User::where('role_id', 3)
            //         ->select('id', 'name', 'email')
            //         ->get();
            // }

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'inst_contacts' => $inst_contacts,
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
     * @function: to fetch Pharmacist list based on user id.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 18 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function fetchPharmacistListByUser($id)
    {
        // die();
        try {
            $pharmacistdetails = PharmacistDetails::where('user_id', $id)->where('reg_status', 'Approved')->orderBy('updated_at', 'desc')->get(['id', 'user_id', 'institution_id']);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'pharmacistdetails' => $pharmacistdetails]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }
    /**
     * @function: to fetch Prescriber list based on user id.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 18 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function fetchPrescriberListByUser($id)
    {
        // die();
        try {
            $prescriberdetails = PrescriberDetails::where('user_id', $id)->where('reg_status', 'Approved')->orderBy('updated_at', 'desc')->get(['id', 'user_id', 'institution_id']);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'prescriberdetails' => $prescriberdetails]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch Institutions by type details.
     *
     * @author: Stalvin M
     *
     * @created-on: 20 April 2026
     *
     * @updated-on: N/A
     */
    public function fetchInstitutionsByType(Request $request)
    {
        try {
            $inst_type = $request->institution_type;
            $user_id = Auth::user()->id;
            $institutions = ConnectedPharmacies::with('pharmacy_data')
                ->where('user_id', $user_id)
                ->whereHas('pharmacy_data', function ($q) use ($inst_type) {
                    $q->where('institution_type', $inst_type);
                })
                ->orderByDesc('updated_at')
                ->get()
                ->map(function ($item) {
                    $pharmacy = $item->pharmacy_data;

                    // Log::info($pharmacy);

                    return [
                        'id' => $pharmacy->id ?? null,
                        'name' => $pharmacy->name ?? null,
                        'institution_type' => $pharmacy->institution_type ?? null,
                        'ref_number' => $pharmacy->ref_number ?? null,
                    ];
                });

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'institutions' => $institutions,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to fetch wholesalers pharmacy with drugs list by type details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 20 April 2026
     *
     * @updated-on: N/A
     */

    public function fetchWholesalerDrugs()
    {
        try {
            $pharmacists = PharmacistDetails::with([
                'user:id,name',
                'medications' => function ($q) {
                    $q->where('expired', 0)
                        ->with('drug:id,drug_name');
                },
            ])->get();

            // Exclude Hospital institutions
            $filtered = $pharmacists->filter(function ($pharmacist) {
                $institution = $pharmacist->institution_data;
                return $institution && $institution->institution_type !== 'Hospital';
            });

            // Group by institution
            $grouped = $filtered->groupBy(function ($pharmacist) {
                return optional($pharmacist->institution_data)->id;
            });

            // Format response
            $formatted = $grouped->map(function ($pharmacists, $institutionId) {

                $institution = optional($pharmacists->first())->institution_data;

                //Collect all drugs from all pharmacists
                $allDrugs = $pharmacists->flatMap(function ($pharmacist) {
                    return $pharmacist->medications;
                });

                //Remove duplicates
                $uniqueDrugs = $allDrugs
                    ->map(function ($med) {
                        return [
                            'drug_id' => $med->drug_id,
                            'drug_name' => optional($med->drug)->drug_name,
                        ];
                    })
                    ->filter(fn($d) => $d['drug_id'] !== null)
                    ->unique('drug_id')
                    ->values();

                return [
                    'institution_id' => $institutionId,
                    'institution_name' => $institution->name ?? null,
                    'institution_type' => $institution->institution_type ?? null,

                    // Only combined drugs
                    'drugs' => $uniqueDrugs,
                ];
            })->values();

            // ONLY CHANGE IS HERE
            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'data' => $formatted, // 👈 keep actual data here
            ]);

        } catch (\Exception $e) {
            \Log::error($e);

            return response()->json([
                'status' => 'E',
                'message' => 'Something went wrong',
                'data' => [],
            ], 500);
        }
    }
    /**
     * @function: to fetch Institutions by type details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 22 April 2026
     *
     * @updated-on: N/A
     */
    public function fetchInstitutionsByUser($id)
    {

        try {

            $institution_ids = PharmacistDetails::where('user_id', $id)
                ->pluck('institution_id')
                ->unique();

            $institutions = Institutions::whereIn('id', $institution_ids)
                ->where('status', 1)
                ->orderBy('id', 'desc')
                ->get(['id', 'name', 'institution_type', 'ref_number']);

            return response()->json([
                'status' => 'S',
                'message' => trans('returnmessage.dataretreived'),
                'institutions' => $institutions,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

}
