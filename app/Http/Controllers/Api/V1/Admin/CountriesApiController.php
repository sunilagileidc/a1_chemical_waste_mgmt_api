<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Cities;
use App\Models\Countries;
use App\Models\States;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;

class CountriesApiController extends Controller
{
    /**
     * @function: to fetch countries data.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function index()
    {
        try {
            $countries = Countries::with('states', 'states.cities')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'countries' => $countries]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch countries data using slug.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function getCountriesBySlug($slug)
    {
        try {
            $countries = Countries::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'countries' => $countries]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch countries data using Id.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function getCountriesById($id)
    {
        try {
            $countries = Countries::where('id', $id)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'countries' => $countries]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to save countries details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 10-02-2026
     */
    public function saveCountries(Request $request)
    {
        $currenttime = date('Y-m-d h:i:s');
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile_code' => 'required',
            'country_code' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($id > 0) {
                $countries = Countries::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'mobile_code' => $request->mobile_code,
                        'country_code' => $request->country_code,
                        'updated_at' => $currenttime,
                    ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'countries' => $countries]);
            } else {
                $countries = Countries::create([
                    'name' => $request->name,
                    'mobile_code' => $request->mobile_code,
                    'country_code' => $request->country_code,
                    'updated_at' => $currenttime,
                    'created_at' => $currenttime,
                ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'countries' => $countries]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delet countries details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function deleteCountries($id)
    {
        try {
            $countries = Countries::where('id', $id)->delete();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update countries status.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function saveStates(Request $request)
    {

        $currenttime = date('Y-m-d h:i:s');
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($id > 0) {
                $states = States::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'updated_at' => $currenttime,
                    ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'states' => $states]);
            } else {
                $states = States::create([
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                    'updated_at' => $currenttime,
                    'created_at' => $currenttime,
                ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'states' => $states]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to setch states details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function fetchStates(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'countryname' => 'required',
        ]);

        try {
            if ($validator->fails()) {
                return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
            }
            $countries = Countries::where('slug', $request->countryname)->first();
            $states = States::where('country_id', $countries->id)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'states' => $states, 'countries' => $countries]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to setch states details using slug.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function getStatesBySlug($slug)
    {
        try {
            $states = States::with('country')->where('slug', $slug)->first();
            $country_id = States::where('slug', $slug)->first('country_id');
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'states' => $states, 'country_id' => $country_id->country_id]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to setch states details using Id.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function getStatesById($id)
    {
        try {
            $states = States::where('id', $id)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'states' => $states]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to setch states name.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function fetchStatesName($id)
    {
        try {
            $name = States::where('country_id', $id)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'states' => $name]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to setch cities name.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function fetchCitiesName($id)
    {
        try {
            $name = Cities::where('state_id', $id)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'cities' => $name]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete states.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function deleteStates($id)
    {
        try {
            $states = States::where('id', $id)->delete();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to save cities details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function saveCities(Request $request)
    {
        // Log::info("REQUEST IN SAVE CITIES");
        // Log::info($request);die;
        $currenttime = date('Y-m-d h:i:s');
        $id = $request->id;
        $validator = Validator::make($request->all(), [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
        }

        try {
            if ($id > 0) {
                $cities = Cities::where('id', $id)
                    ->update([
                        'name' => $request->name,
                        'updated_at' => $currenttime,
                    ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'cities' => $cities]);
            } else {
                $cities = Cities::create([
                    'name' => $request->name,
                    'country_id' => $request->country_id,
                    'state_id' => $request->state_id,
                    'updated_at' => $currenttime,
                    'created_at' => $currenttime,
                ]);
                return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'cities' => $cities]);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch cities details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function fetchCities(Request $request)
    {
        Log::info("REQUEST IN CITIES");
        Log::info($request); //die;

        $validator = Validator::make($request->all(), [
            'countryname' => 'required',
            'statename' => 'required',
        ]);

        try {
            if ($validator->fails()) {
                return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
            }
            $state_id = States::where('slug', $request->statename)->first();
            $sid = $state_id->id;
            $countries = Countries::where('slug', $request->countryname)->first();

            $cities = Cities::where('state_id', $sid)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'cities' => $cities, 'states' => $state_id, 'countries' => $countries]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch cities details using slug.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function getCitiesBySlug($slug)
    {
        try {
            $cities = Cities::with('state', 'country')->where('slug', $slug)->first();
            $state_id = Cities::where('slug', $slug)->first('state_id');
            $country_id = Cities::where('slug', $slug)->first('country_id');
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'cities' => $cities, 'state_id' => $state_id->state_id, 'country_id' => $country_id->country_id]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete cities details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 05-02-2026
     *
     * @updated-on: 05-02-2026
     */
    public function deleteCities($id)
    {
        try {
            $cities = Cities::where('id', $id)->delete();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    /**
     * @function: to update System Parameters 'is whitelisted' status.
     *
     * @author: Santhosha G
     *
     * @created-on: 10 Feb, 2026
     *
     * @updated-on: N/A
     */
    public function updateCountryStatus(request $request)
    {
        try {
            $Countries = Countries::where('id', $request->id)->first();
            if ($Countries->is_whitelisted == 1) {
                $status = Countries::where('id', $request->id)->update(['is_whitelisted' => 0]);
            } else {
                $status = Countries::where('id', $request->id)->update(['is_whitelisted' => 1]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
