<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\LookUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LookupsApiController extends Controller
{
    /**
     * @function: to fetch Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function index(Request $request)
    {
        try {
            $lookups = LookUp::orderBy('created_at', 'desc')->where('parent_id', 0)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'lookups' => $lookups]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        try {
            if ($request->slug) {
                $lookups = LookUp::where('slug', $request->slug)->first();
                $request['parent_id'] = $lookups->id;
            }
            $lookup = LookUp::create($request->all());
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'lookup' => $lookup]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function edit($slug)
    {
        try {
            $lookup = LookUp::with('parentlookup')->where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.data_return'), 'lookup' => $lookup]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $id)
    {
        try {
            $lookup = LookUp::findOrFail($id);
            $lookup->update($request->all());
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try {
            LookUp::destroy($id);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch parent Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function parentlookups()
    {
        try {
            $lookups = LookUp::where('parent_id', 0)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'lookups' => $lookups]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function lookupdata($type)
    {
        try {
            $Parentlookupid = LookUp::where('shortname', $type)->first();
            $lookups = LookUp::where('parent_id', $Parentlookupid->id)->distinct()->orderBy('seq', 'asc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'lookups' => $lookups]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch Lookup details.
     *
     * @author: Suprith S
     *
     * @created-on: 4 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchLookup(Request $request)
    {
        $lookup_type = $request->lookup_type;
        $validator = Validator::make($request->all(), [
            'lookup_type' => 'required',
        ]);
        try {
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()]);
            } else {
                $parent = LookUp::where('shortname', $lookup_type)->where('status', 1)->first();
                if ($parent) {
                    $childs = LookUp::where('parent_id', $parent->id)->where('status', 1)->orderBy('seq', 'asc')->get();
                    if (count($childs) == 0) {
                        return response()->json(['status' => 'E', 'message' => trans('returnmessage.details_not_found')]);
                    }
                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.lookup_values'), 'lookup_details' => $childs]);
                } else {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.details_not_found')]);
                }
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function updateLookupStatus(request $request)
    {
        try {
            $LookUp = LookUp::where('id', $request->id)->first();
            if ($LookUp->status == 1) {
                $status = LookUp::where('id', $request->id)->update(['status' => 0]);
            } else {
                $status = LookUp::where('id', $request->id)->update(['status' => 1]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function store_lookups(Request $request)
    {
        $currenttime = date('Y-m-d H:i:s');
        $id = $request->id ?? 0;

        try {

            $request->validate([
                'shortname' => [
                    'required',
                    Rule::unique('lookups', 'shortname')->ignore($id),
                ],
                'longname' => 'required',
                'description' => 'nullable',
            ]);

            if ($id > 0) {

                LookUp::where('id', $id)->update([
                    'shortname' => $request->shortname,
                    'longname' => $request->longname,
                    'description' => $request->description,
                    'updated_at' => $currenttime,
                ]);

                $lookup = LookUp::find($id);

                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                    'lookups' => $lookup,
                ]);

            } else {

                $lookup = LookUp::create([
                    'shortname' => $request->shortname,
                    'longname' => $request->longname,
                    'description' => $request->description,
                    'created_at' => $currenttime,
                ]);

                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.createdsuccessfully'),
                    'lookups' => $lookup,
                ]);
            }

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'errordata' => $e->getMessage(),
            ], 500);
        }
    }

    public function store_child_lookups(Request $request)
    {
        $currenttime = date('Y-m-d H:i:s');
        $id = $request->id ?? 0;

        try {

            if ($id > 0) {
                $existing = LookUp::find($id);
                $parentId = $existing->parent_id;
            } else {
                $parent = LookUp::where('slug', $request->parentslug)->first();

                if (!$parent) {
                    return response()->json([
                        'status' => 'E',
                        'message' => 'Parent lookup not found',
                    ]);
                }

                $parentId = $parent->id;
            }

            // Validation (unique per parent_id)
            $request->validate([
                'shortname' => [
                    'required',
                    Rule::unique('lookups')
                        ->where(function ($query) use ($parentId) {
                            return $query->where('parent_id', $parentId);
                        })
                        ->ignore($id),
                ],
                'longname' => 'required',
                'seq' => 'required',
                'description' => 'nullable',
            ]);

            if ($id > 0) {

                LookUp::where('id', $id)->update([
                    'shortname' => $request->shortname,
                    'longname' => $request->longname,
                    'description' => $request->description,
                    'seq' => $request->seq,
                    'updated_at' => $currenttime,
                ]);

                $lookup = LookUp::find($id);

                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.updatedsuccessfully'),
                    'lookups' => $lookup,
                ]);

            } else {

                $lookup = LookUp::create([
                    'shortname' => $request->shortname,
                    'longname' => $request->longname,
                    'description' => $request->description,
                    'seq' => $request->seq,
                    'parent_id' => $parentId,
                    'created_at' => $currenttime,
                ]);

                return response()->json([
                    'status' => 'S',
                    'message' => trans('returnmessage.createdsuccessfully'),
                    'lookups' => $lookup,
                ]);
            }

        } catch (\Exception $e) {

            return response()->json([
                'status' => 'E',
                'message' => trans('returnmessage.error_processing'),
                'errordata' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchParentLookup(Request $request)
    {
        try {
            $lookups = LookUp::where('slug', $request->slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'lookups' => $lookups]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }

    }
}
