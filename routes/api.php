<?php

use App\Http\Controllers\Api\V1\Admin\ActionMasterApiController;
use App\Http\Controllers\Api\V1\Admin\ApprovalApiController;
use App\Http\Controllers\Api\V1\Admin\AuditApiController;
use App\Http\Controllers\Api\V1\Admin\ChangePasswordApiController;
use App\Http\Controllers\Api\V1\Admin\CountriesApiController;
use App\Http\Controllers\Api\V1\Admin\CustomerApiController;
use App\Http\Controllers\Api\V1\Admin\CustomerIndividualApiController;
use App\Http\Controllers\Api\V1\Admin\DocumentApiController;
use App\Http\Controllers\Api\V1\Admin\FileUploadApiController;
use App\Http\Controllers\Api\V1\Admin\HaulierApiController;
use App\Http\Controllers\Api\V1\Admin\HaulierIndividualApiController;
use App\Http\Controllers\Api\V1\Admin\IndicationsApiController;
use App\Http\Controllers\Api\V1\Admin\LookupsApiController;
use App\Http\Controllers\Api\V1\Admin\MarketingHoldersApiController;
use App\Http\Controllers\Api\V1\Admin\MenuApiController;
use App\Http\Controllers\Api\V1\Admin\QuotationPartnerApiController;
use App\Http\Controllers\Api\V1\Admin\RolesApiController;
use App\Http\Controllers\Api\V1\Admin\SalesQuotationApiController;
use App\Http\Controllers\Api\V1\Admin\SupplierApiController;
use App\Http\Controllers\Api\V1\Admin\SupplierIndividualApiController;
use App\Http\Controllers\Api\V1\Admin\SupplierSalesDataApiController;
use App\Http\Controllers\Api\V1\Admin\SystemParameterApiController;
use App\Http\Controllers\Api\V1\Admin\UserApiController;
use App\Http\Controllers\Api\V1\Admin\WasteStreamApiController;
use App\Http\Controllers\Api\V1\Auth\LoginApiController;
use App\Http\Controllers\Api\V1\Auth\RecoverPasswordApiController;
use App\Http\Controllers\Api\V1\Auth\UserRegistrationApiController;

//registration
Route::post('register', [LoginApiController::class, 'register']);
Route::middleware(['throttle:20,1'])->post('prescriber_register', [UserRegistrationApiController::class, 'prescriberRegister']);
Route::middleware(['throttle:20,1'])->post('pharmacist_register', [UserRegistrationApiController::class, 'pharmacistRegister']);

//Login
Route::middleware('throttle:5,2')->post('/login', [LoginApiController::class, 'login']);
Route::get('/fetch_image_url', [SystemParameterApiController::class, 'fetchImageUrl']);
Route::get('/update_status', [SystemParameterApiController::class, 'fetchImageUrl']);
//Password Reset and Resend Otp
Route::middleware('throttle:5,2')->post('reset_password', [RecoverPasswordApiController::class, 'sendPasswordReset']);
Route::middleware('throttle:5,2')->post('/resetuserpassword', [ChangePasswordApiController::class, 'changePassword']);
Route::middleware('throttle:5,2')->post('resend_otp_validate', [RecoverPasswordApiController::class, 'sendRegistrationOtp']);
Route::middleware('throttle:5,2')->post('registration_otp_validate', [UserRegistrationApiController::class, 'validateRegistrationOtp']);
Route::middleware('throttle:5,2')->post('resetpassword', [RecoverPasswordApiController::class, 'validateOtp']);
Route::middleware('throttle:5,2')->post('login_otp_validate', [RecoverPasswordApiController::class, 'loginOtpValidate']);
Route::middleware('throttle:5,2')->post('send_login_otp', [LoginApiController::class, 'sendLoginOtp']);

Route::middleware('auth:api')->group(function () {
Route::get('profile', [LoginApiController::class, 'profile']);
Route::post('logout', [LoginApiController::class, 'logout']);
Route::post('unlock_user', [LoginApiController::class, 'unlockUser']);
Route::post('/verify_retrospective', [LoginApiController::class, 'verifyRetrospective']);

// RolesApiController
Route::resource('roles', RolesApiController::class);
Route::get('/fetchrole', [RolesApiController::class, 'fetchRole']);

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
Route::get('fetchuserbyslug/{slug}', [UserApiController::class, 'fetchUserBySlug']);
Route::post('/saveuser', [UserApiController::class, 'saveuser']);
Route::post('updateuserstatus', [UserApiController::class, 'updateUserStatus']);
Route::post('update_pharmacist_role', [UserApiController::class, 'updatePharmacistRole']);
Route::post('update_suspicious_actor', [UserApiController::class, 'updateSuspiciousActor']);
Route::post('updateprofile', [UserApiController::class, 'updateProfile']);
Route::post('/send_credentials', [UserApiController::class, 'sendCredentials']);
// Route::post('unlock_user', [UserApiController::class, 'unlockUser']);
Route::get('/fetch_locked_users', [UserApiController::class, 'fetchLockedUser']);
Route::get('/fetch_connected_nurses', [UserApiController::class, 'fetchConnectedNurses']);
Route::get('fetch_connected_nurses_byslug/{slug}', [UserApiController::class, 'fetchConnectedNursesBySlug']);
Route::get('/get_wholesaler_accounts', [UserApiController::class, 'getUserWholesalers']);

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

// Action Master
Route::resource('/action_master', ActionMasterApiController::class);
Route::post('/update_action_master_status', [ActionMasterApiController::class, 'updateActionMasterStatus']);
Route::get('/fetchroleactions/{role_id}', [ActionMasterApiController::class, 'fetchRoleActions']);
Route::post('/storeactionaccess', [ActionMasterApiController::class, 'storeRoleAction']);
Route::get('/check_action_permission', [ActionMasterApiController::class, 'checkActionPermission']);

//File Upload Method
Route::post('/imageupload', [FileUploadApiController::class, 'imageUpload']);
Route::post('/file_upload', [FileUploadApiController::class, 'fileUpload']);
Route::post('/imageUrlBase64', [FileUploadApiController::class, 'imageUrlBase64']);

// Document Controller
Route::get('/fetch_documents', [DocumentApiController::class, 'index']);
Route::get('/fetch_chid_documents/{slug}', [DocumentApiController::class, 'fetchChidDocuments']);
Route::get('/fetch_document_details', [DocumentApiController::class, 'fetchDocumentDetails']);
Route::get('/fetch_latest_document_details', [DocumentApiController::class, 'fetchLatestDocumentDetails']);
Route::get('/documents/{slug}', [DocumentApiController::class, 'editDocument']);
Route::post('/create_document', [DocumentApiController::class, 'store']);
Route::patch('/documents/{id}', [DocumentApiController::class, 'update']);
Route::post('/upload_file', [DocumentApiController::class, 'uploadFile']);
Route::delete('/documents/{id}', [DocumentApiController::class, 'delete']);
Route::get('/fetch_documents/{search?}', [DocumentApiController::class, 'fetchDocuments']);
Route::get('/fetch_raf_documents', [DocumentApiController::class, 'fetchRafDocuments']);
Route::post('/download_notification', [DocumentApiController::class, 'downloadNotification']);

// // Prescriber Drug controller
// Route::get('/fetch_drug', [PAFDrugApiController::class, 'index']);
// Route::post('/create_drug', [PAFDrugApiController::class, 'store']);
// Route::get('/edit_drug/{slug}', [PAFDrugApiController::class, 'editDrug']);
// Route::delete('/drug/{id}', [PAFDrugApiController::class, 'deleteDrug']);

//Approval Controller
Route::get('/fetch_reg_list', [ApprovalApiController::class, 'index']);
Route::get('/fetch_regdetails_by_slug/{slug}', [ApprovalApiController::class, 'fetchRegDetailsBySlug']);
Route::post('/updateRegStatus', [ApprovalApiController::class, 'updateRegStatus']);
Route::get('/fetch_pharmacist_list', [ApprovalApiController::class, 'fetchAllPharmacist']);

//Indications Api Controller
Route::get('/fetch_all_indications', [IndicationsApiController::class, 'index']);
Route::delete('/indication/{id}', [IndicationsApiController::class, 'destroy']);
Route::get('/edit_indications/{slug}', [IndicationsApiController::class, 'editIndications']);
Route::post('/indication', [IndicationsApiController::class, 'store']);
Route::post('/update_indication_status', [IndicationsApiController::class, 'updateIndicationStatus']);
Route::get('/fetch_indications', [IndicationsApiController::class, 'fetchIndications']);

//Marketing holder Api Controller
Route::get('/fetch_all_marketing_holders', [MarketingHoldersApiController::class, 'index']);
Route::delete('/marketing_holder/{id}', [MarketingHoldersApiController::class, 'destroy']);
Route::get('/edit_marketing_holder/{slug}', [MarketingHoldersApiController::class, 'editMarketingHolder']);
Route::post('/marketing_holder', [MarketingHoldersApiController::class, 'store']);
Route::post('/update_marketing_holder_status', [MarketingHoldersApiController::class, 'updateMarketingHolderStatus']);
Route::get('/fetch_marketing_holders', [MarketingHoldersApiController::class, 'fetchMarketingHolders']);
Route::get('/fetch_marketing_holders_by_drug', [MarketingHoldersApiController::class, 'fetchMarketingHoldersById']);

Route::get('/fetch_all_audits', [AuditApiController::class, 'index']);


// Supplier Sales Data
Route::get('/get_supplier_sales_data', [SupplierSalesDataApiController::class, 'index']);
Route::post('upload_supplier_sales_data', [SupplierSalesDataApiController::class, 'upload']);

// Customer Api Controller
Route::get('customers', [CustomerApiController::class, 'index']);
Route::get('customer/{id}', [CustomerApiController::class, 'getCustomerById']);
Route::get('customerbyslug/{slug}', [CustomerApiController::class, 'getCustomerBySlug']);
Route::post('savecustomer', [CustomerApiController::class, 'saveCustomer']);
Route::delete('deletecustomer/{id}', [CustomerApiController::class, 'deleteCustomer']);
Route::post('updatecustomerstatus', [CustomerApiController::class, 'updateCustomerStatus']);

//Supplier Api Controller
Route::get('suppliers', [SupplierApiController::class, 'index']);
Route::get('supplier/{id}', [SupplierApiController::class, 'getSupplierById']);
Route::get('supplierbyslug/{slug}', [SupplierApiController::class, 'getSupplierBySlug']);
Route::post('savesupplier', [SupplierApiController::class, 'saveSupplier']);
Route::delete('deletesupplier/{id}', [SupplierApiController::class, 'deleteSupplier']);
Route::post('updatesupplierstatus', [SupplierApiController::class, 'updateSupplierStatus']);

//Haulier Api Controller
Route::get('hauliers', [HaulierApiController::class, 'index']);
Route::get('haulier/{id}', [HaulierApiController::class, 'getHaulierById']);
Route::get('haulierbyslug/{slug}', [HaulierApiController::class, 'getHaulierBySlug']);
Route::post('savehaulier', [HaulierApiController::class, 'saveHaulier']);
Route::delete('deletehaulier/{id}', [HaulierApiController::class, 'deleteHaulier']);
Route::post('updatehaulierstatus', [HaulierApiController::class, 'updateHaulierStatus']);

//Customer Individual Api Controller
Route::get('customercontacts', [CustomerIndividualApiController::class, 'index']);
Route::get('customercontactsbycustomer/{id}', [CustomerIndividualApiController::class, 'getContactsByCustomer']);
Route::get('customerindividual/{id}', [CustomerIndividualApiController::class, 'getById']);
Route::get('customercontact/{id}', [CustomerIndividualApiController::class, 'getContactById']);
Route::post('savecustomercontact', [CustomerIndividualApiController::class, 'saveContact']);
Route::delete('deletecustomercontact/{id}', [CustomerIndividualApiController::class, 'deleteContact']);
Route::post('updatecustomercontactstatus', [CustomerIndividualApiController::class, 'updateContactStatus']);

//Supplier Individual Api Controller
Route::get('suppliercontacts', [SupplierIndividualApiController::class, 'index']);
Route::get('suppliercontactsby supplier/{id}', [SupplierIndividualApiController::class, 'getContactsBySupplier']);
Route::get('supplierindividual/{id}', [SupplierIndividualApiController::class, 'getById']);
Route::get('suppliercontact/{id}', [SupplierIndividualApiController::class, 'getContactById']);
Route::post('savesuppliercontact', [SupplierIndividualApiController::class, 'saveContact']);
Route::delete('deletesuppliercontact/{id}', [SupplierIndividualApiController::class, 'deleteContact']);
Route::post('updatesuppliercontactstatus', [SupplierIndividualApiController::class, 'updateContactStatus']);

//Haulier Individual Api Controller
Route::get('hauliercontacts', [HaulierIndividualApiController::class, 'index']);
Route::get('hauliercontactsby haulier/{id}', [HaulierIndividualApiController::class, 'getContactsByHaulier']);
Route::get('haulierindividual/{id}', [HaulierIndividualApiController::class, 'getById']);
Route::get('hauliercontact/{id}', [HaulierIndividualApiController::class, 'getContactById']);
Route::post('savehauliercontact', [HaulierIndividualApiController::class, 'saveContact']);
Route::delete('deletehauliercontact/{id}', [HaulierIndividualApiController::class, 'deleteContact']);
Route::post('updatehauliercontactstatus', [HaulierIndividualApiController::class, 'updateContactStatus']);

//Waste Stream Api Controller
Route::get('wastestreams', [WasteStreamApiController::class, 'index']);
Route::post('savewastestream', [WasteStreamApiController::class, 'save']);
Route::get('wastestreambyslug/{slug}', [WasteStreamApiController::class, 'bySlug']);

// Sales Quotation Api Controller
Route::get('salesquotations', [SalesQuotationApiController::class, 'index']);
Route::post('savesalesquotation', [SalesQuotationApiController::class, 'save']);
Route::get('salesquotationbyslug/{slug}', [SalesQuotationApiController::class, 'bySlug']);

// Quotation Partner Api Controller
Route::get('quotationpartners/{quotation_id}', [QuotationPartnerApiController::class, 'index']);
Route::post('savequotationpartner', [QuotationPartnerApiController::class, 'save']);
Route::get('quotationpartner/{id}', [QuotationPartnerApiController::class, 'edit']);

});
