<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\V1\Admin\CountriesApiController;
use App\Http\Controllers\Api\V1\Admin\ChangePasswordApiController;
use App\Http\Controllers\Api\V1\Admin\FileUploadApiController;
use App\Http\Controllers\Api\V1\Admin\LookupsApiController;
use App\Http\Controllers\Api\V1\Admin\MenuApiController;
use App\Http\Controllers\Api\V1\Admin\RolesApiController;
use App\Http\Controllers\Api\V1\Admin\SystemParameterApiController;
use App\Http\Controllers\Api\V1\Admin\UserApiController;
use App\Http\Controllers\Api\V1\Auth\RecoverPasswordApiController;
use App\Http\Controllers\Api\V1\Auth\UserRegistrationApiController;
use Illuminate\Support\Facades\Route;

//registration
Route::post('register', [AuthController::class, 'register']);

//Login
Route::middleware('throttle:5,2')->post('/login', [AuthController::class, 'login']);
Route::get('/fetch_image_url', [SystemParameterApiController::class, 'fetchImageUrl']);
//Password Reset and Resend Otp
Route::middleware('throttle:5,2')->post('reset_password', [RecoverPasswordApiController::class, 'sendPasswordReset']);
Route::middleware('throttle:5,2')->post('/resetuserpassword', [ChangePasswordApiController::class, 'changePassword']);
Route::middleware('throttle:5,2')->post('resend_otp_validate', [RecoverPasswordApiController::class, 'sendRegistrationOtp']);
Route::middleware('throttle:5,2')->post('registration_otp_validate', [UserRegistrationApiController::class, 'validateRegistrationOtp']);
Route::middleware('throttle:5,2')->post('resetpassword', [RecoverPasswordApiController::class, 'validateOtp']);
Route::middleware('throttle:5,2')->post('login_otp_validate', [RecoverPasswordApiController::class, 'loginOtpValidate']);
Route::middleware('throttle:5,2')->post('send_login_otp', [AuthController::class, 'sendLoginOtp']);

Route::middleware('auth:api')->group(function () {
    Route::get('profile', [AuthController::class, 'profile']);
    Route::post('logout', [AuthController::class, 'logout']);

// RolesApiController
    Route::resource('roles', RolesApiController::class);

//MenuApiController
    Route::post('menutree', [MenuApiController::class, 'menutree']);
    Route::resource('menu', MenuApiController::class);
    Route::get('rolemenu', [MenuApiController::class, 'rolemenu']);
    Route::get('getmenuaccess/{roleid}', [MenuApiController::class, 'getmenuaccess']);
    Route::post('storemenuaccess', [MenuApiController::class, 'storemenuaccess']);
    Route::get('parentmenus', [MenuApiController::class, 'parentMenus']);

// Email Templates
    Route::resource('emailtemplates', 'App\Http\Controllers\Api\V1\Admin\EmailTemplateApiController');

//LookupsApiController
    Route::get('lookupdata/{type}', [LookupsApiController::class, 'lookupdata']);
    Route::get('child_lookups_edit', [LookupsApiController::class, 'childLookupEdit']);
    Route::resource('lookups', LookupsApiController::class);
    Route::get('/fetchlookup', [LookupsApiController::class, 'fetchLookup']);
    Route::get('/fetch_lang_lookup', [LookupsApiController::class, 'fetchLangLookup']);
    Route::post('/update_lookups_status', [LookupsApiController::class, 'updateLookupStatus']);
    Route::post('/fetch_parent_lookup', [LookupsApiController::class, 'fetchParentLookup']);
    Route::post('/save_lookups', [LookupsApiController::class, 'store_lookups']);
    Route::post('/save_child_lookups', [LookupsApiController::class, 'store_child_lookups']);
    Route::post('/delete_lookup/{id}', [LookupsApiController::class, 'destroy']);

//UserApiController
    Route::get('/fetchuser', [UserApiController::class, 'fetchUser']);
    Route::get('/fetchDashboardSuperUser/{user_id}', [UserApiController::class, 'fetchDashboardSuperUser']);
    Route::get('/fetchDashboardMallAdmin/{user_id}', [UserApiController::class, 'fetchDashboardMallAdmin']);
    Route::get('/fetchDashboardStoreAdmin/{user_id}', [UserApiController::class, 'fetchDashboardStoreAdmin']);
    Route::get('fetchuserdatabyslug/{slug}', [UserApiController::class, 'fetchUserDataBySlug']);

// CountriesApiController
    Route::get('/fetch_countries', [CountriesApiController::class, 'index']);
    Route::post('/save_countries', [CountriesApiController::class, 'saveCountries']);
    Route::get('/edit_countries/{slug}', [CountriesApiController::class, 'getCountriesBySlug']);
    Route::get('/fetch_edit_countries/{id}', [CountriesApiController::class, 'getCountriesById']);
    Route::post('/delete_countries/{id}', [CountriesApiController::class, 'deleteCountries']);
    Route::get('/fetch_states', [CountriesApiController::class, 'fetchStates']);
    Route::get('/fetch_states_name/{id}', [CountriesApiController::class, 'fetchStatesName']);
    Route::post('/save_states', [CountriesApiController::class, 'saveStates']);
    Route::get('/edit_states/{slug}', [CountriesApiController::class, 'getStatesBySlug']);
    Route::get('/fetch_edit_states/{id}', [CountriesApiController::class, 'getStatesById']);
    Route::post('/delete_states/{id}', [CountriesApiController::class, 'deleteStates']);
    Route::get('/fetch_cities', [CountriesApiController::class, 'fetchCities']);
    Route::get('/fetch_cities_name/{id}', [CountriesApiController::class, 'fetchCitiesName']);
    Route::post('/save_cities', [CountriesApiController::class, 'saveCities']);
    Route::get('/edit_cities/{slug}', [CountriesApiController::class, 'getCitiesBySlug']);
    Route::post('/delete_cities/{id}', [CountriesApiController::class, 'deleteCities']);
    Route::post('/update_country_status', [CountriesApiController::class, 'updateCountryStatus']);

    // System Parameters
    Route::get('/getsystem_params', [SystemParameterApiController::class, 'index']);
    Route::post('/save_system_params', [SystemParameterApiController::class, 'store']);
    Route::get('/edit_system_params/{slug}', [SystemParameterApiController::class, 'editSystemParameter']);
    Route::post('/delete_system_params/{id}', [SystemParameterApiController::class, 'deleteSystemParameter']);
    Route::get('/fetch_system_params', [SystemParameterApiController::class, 'fetchsystemparameter']);
    Route::post('/update_system_param_status', [SystemParameterApiController::class, 'updateSystemParamStatus']);
    Route::get('/fetch_products_tags_status', [SystemParameterApiController::class, 'fetchProductsTagsStatus']);

    //File Upload Method
    Route::post('/imageupload', [FileUploadApiController::class, 'imageUpload']);
    Route::post('/file_upload', [FileUploadApiController::class, 'fileUpload']);
    Route::post('/imageUrlBase64', [FileUploadApiController::class, 'imageUrlBase64']);

});
