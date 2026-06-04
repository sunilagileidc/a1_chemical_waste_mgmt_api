<?php

use App\Http\Controllers\Api\V1\Admin\ActionMasterApiController;
use App\Http\Controllers\Api\V1\Admin\ApprovalApiController;
use App\Http\Controllers\Api\V1\Admin\AuditApiController;
use App\Http\Controllers\Api\V1\Admin\ChangePasswordApiController;
use App\Http\Controllers\Api\V1\Admin\ConnectedPharmacyApiController;
use App\Http\Controllers\Api\V1\Admin\CountriesApiController;
use App\Http\Controllers\Api\V1\Admin\CustomerApiController;
use App\Http\Controllers\Api\V1\Admin\CustomerIndividualApiController;
use App\Http\Controllers\Api\V1\Admin\DocumentApiController;
use App\Http\Controllers\Api\V1\Admin\DrugsApiController;
use App\Http\Controllers\Api\V1\Admin\FileUploadApiController;
use App\Http\Controllers\Api\V1\Admin\HaulierApiController;
use App\Http\Controllers\Api\V1\Admin\HaulierIndividualApiController;
use App\Http\Controllers\Api\V1\Admin\IndicationsApiController;
use App\Http\Controllers\Api\V1\Admin\InstitutionApiController;
use App\Http\Controllers\Api\V1\Admin\LookupsApiController;
use App\Http\Controllers\Api\V1\Admin\MarketingHoldersApiController;
use App\Http\Controllers\Api\V1\Admin\MenuApiController;
use App\Http\Controllers\Api\V1\Admin\NonConformaceRulesApiController;
use App\Http\Controllers\Api\V1\Admin\PAFApiController;
use App\Http\Controllers\Api\V1\Admin\PAFConfirmationTextApiController;
use App\Http\Controllers\Api\V1\Admin\PAFDocumentApiController;
use App\Http\Controllers\Api\V1\Admin\PharmacyApiController;
use App\Http\Controllers\Api\V1\Admin\PolicyAssignQuestionsApiController;
use App\Http\Controllers\Api\V1\Admin\PolicyQuestionsApiController;
use App\Http\Controllers\Api\V1\Admin\QuotationPartnerApiController;
use App\Http\Controllers\Api\V1\Admin\RolesApiController;
use App\Http\Controllers\Api\V1\Admin\SalesQuotationApiController;
use App\Http\Controllers\Api\V1\Admin\SupplierApiController;
use App\Http\Controllers\Api\V1\Admin\SupplierIndividualApiController;
use App\Http\Controllers\Api\V1\Admin\SupplierSalesDataApiController;
use App\Http\Controllers\Api\V1\Admin\SystemParameterApiController;
use App\Http\Controllers\Api\V1\Admin\UserApiController;
use App\Http\Controllers\Api\V1\Admin\WasteStreamApiController;
use App\Http\Controllers\Api\V1\Admin\WolesalersApiController;
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

//routes without auth sanctum
Route::get('/fetchactiveinstitutions', [InstitutionApiController::class, 'fetchActiveInstitutions']);
Route::get('/fetchlookup', [LookupsApiController::class, 'fetchLookup']);
Route::get('/fetch_institution_by_type', [InstitutionApiController::class, 'fetchInstitutionByType']);
Route::get('/fetch_pharmacist_list_by_user/{id}', [InstitutionApiController::class, 'fetchPharmacistListByUser']);
Route::get('/fetch_prescriber_list_by_user/{id}', [InstitutionApiController::class, 'fetchPrescriberListByUser']);
Route::get('/fetch_active_drugs', [DrugsApiController::class, 'fetchActiveDrugs']);
Route::get('/fetch_active_wholesalers', [WolesalersApiController::class, 'fetchActiveWholesalers']);
Route::get('fetch_systemparameter_data', [SystemParameterApiController::class, 'getSystemParameter']);
Route::get('/fetch_policy_questions', [PolicyQuestionsApiController::class, 'fetchRegPolicyQuestions']);
Route::post('/check-email-exists', [UserRegistrationApiController::class, 'checkEmailExists']);
Route::get('/fetch_all_drugs', [DrugsApiController::class, 'fetchAllDrugs']);
Route::get('/fetch_wholesaler_drugs', [InstitutionApiController::class, 'fetchWholesalerDrugs']);
Route::get('/fetch_institutions_by_user/{id}', [InstitutionApiController::class, 'fetchInstitutionsByUser']);
Route::post('/save_wholesaler_accounts', [WolesalersApiController::class, 'saveWholesalerAccounts']);
Route::get('/check-lead-pharmacist/{institution_id}', [DrugsApiController::class, 'checkLeadPharmacist']);

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

// Institution Api Controller
Route::resource('institution', InstitutionApiController::class);
Route::post('/update_institution_status', [InstitutionApiController::class, 'updateInstitutionStatus']);
Route::get('/fetchactivepharmacies', [InstitutionApiController::class, 'fetchActivePharmacies']);
Route::get('/fetch_inst_contacts', [InstitutionApiController::class, 'fetchInstitutionContacts']);
Route::get('/fetch_institutions_by_type', [InstitutionApiController::class, 'fetchInstitutionsByType']);

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

// Drugs controller
Route::get('/fetch_drugs', [DrugsApiController::class, 'index']);
Route::post('/update_drug_status', [DrugsApiController::class, 'updateDrugStatus']);

// Drug controller
Route::get('/fetch_drug', [DrugsApiController::class, 'index']);
Route::post('/create_drug', [DrugsApiController::class, 'store']);
Route::get('/edit_drug/{slug}', [DrugsApiController::class, 'editDrug']);
Route::delete('/drug/{id}', [DrugsApiController::class, 'deleteDrug']);
Route::post('/update_drug', [DrugsApiController::class, 'updateDrugs']);
Route::post('/force_to_re_register', [DrugsApiController::class, 'forceToReRegister']);
Route::post('/force_to_re_register_selected_drug', [DrugsApiController::class, 'forceToReRegisterSelectedDrug']);
Route::get('/get_reg_drug_status', [DrugsApiController::class, 'getRegisteredDrugStatus']);
Route::get('/get_inst_drugs', [DrugsApiController::class, 'getInstitutionDrugs']);
Route::get('/get_unregistred_drugs', [DrugsApiController::class, 'getUnregisteredDrugs']);
Route::post('/force_to_re_register_drug_level', [DrugsApiController::class, 'forceToReRegisterDrugLevel']);

// Wholesaler controller
Route::get('/fetch_wholesalers', [WolesalersApiController::class, 'index']);
Route::post('/create_wholesaler', [WolesalersApiController::class, 'store']);
Route::get('/edit_wholesaler/{slug}', [WolesalersApiController::class, 'edit']);
Route::patch('/update_wholesaler/{id}', [WolesalersApiController::class, 'update']);
Route::post('/update_wholesaler_status', [WolesalersApiController::class, 'updateWholesalerStatus']);

//policy questions
Route::resource('/policy_questions', PolicyQuestionsApiController::class);
Route::post('/update_policy_question_status', [PolicyQuestionsApiController::class, 'updatePolicyQuestionStatus']);

//policy assign questions
Route::resource('/assign_policy_questions', PolicyAssignQuestionsApiController::class);
Route::post('/update_assign_policy_question_status', [PolicyAssignQuestionsApiController::class, 'updateAssignPolicyQuestionStatus']);
// Parmacy Api Controller
Route::resource('/pharmacy', PharmacyApiController::class);
Route::post('/update_pharmacy_status', [PharmacyApiController::class, 'updatePharmacyStatus']);

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

Route::resource('/connected_pharmacy', ConnectedPharmacyApiController::class);
Route::get('/connected_outpatient', [ConnectedPharmacyApiController::class, 'getConnectedOutpatient']);
Route::get('/connected_homecare', [ConnectedPharmacyApiController::class, 'getConnectedHomecare']);
Route::post('/update_connected_pharmacy', [ConnectedPharmacyApiController::class, 'updateConnPharmacyStatus']);

// PAF apis

Route::get('/fetch_prescriber_data', [PAFApiController::class, 'fetchPrescriberData']);
Route::get('/get_prescriber_drugs', [PAFApiController::class, 'getPrescriberDrugs']);
Route::get('/fetch_drug_details', [PAFApiController::class, 'fetchDrugDetails']);
Route::post('/create_paf', [PAFApiController::class, 'createPaf']);
Route::get('/get_prescriber_paf', [PAFApiController::class, 'getPrescriberPaf']);
Route::get('/get_pharmacist_paf', [PAFApiController::class, 'getPharmacistPaf']);
Route::get('/get_paf_stats', [PAFApiController::class, 'getPafStats']);
Route::get('/get_pharmacist_paf_stats', [PAFApiController::class, 'getPharmacistPafStats']);
Route::get('/paf_details', [PAFApiController::class, 'getPafDetails']);
Route::post('/reject_paf', [PAFApiController::class, 'rejectPaf']);
Route::get('/fetch_patient_initials', [PAFApiController::class, 'fetchPatientInitials']);
Route::post('/paf_approve_and_dispense', [PAFApiController::class, 'pafApproveAndDispense']);
Route::post('/paf_approve', [PAFApiController::class, 'pafApprove']);
Route::get('/get_paf_history', [PAFApiController::class, 'fetchPafHistory']);
Route::get('/get_paf_by_details_id', [PAFApiController::class, 'getPafByDetailsId']);
Route::post('/paf_revert', [PAFApiController::class, 'pafRevert']);
Route::get('/get_all_pafs', [PAFApiController::class, 'getAllPafs']);
Route::get('/get_all_paf_stats', [PAFApiController::class, 'getAllPafStats']);
Route::get('/get_all_paf_details', [PAFApiController::class, 'getAllPafDetails']);
Route::post('/bulk_review_paf', [PAFApiController::class, 'bulkPAFReview']);
Route::post('/validate_paf_conformance', [PAFApiController::class, 'validatePafConformance']);
Route::post('/merge_paf', [PAFApiController::class, 'mergePaf']);
Route::post('/check_existing_active_paf', [PAFApiController::class, 'checkExistingActivePaf']);
Route::get('/get_off_lable_pafs', [PAFApiController::class, 'getOffLablePafs']);
Route::post('/send_paf_request_info', [PAFApiController::class, 'sendPafRequestInfo']);
Route::get('/get_all_paf_counts', [PAFApiController::class, 'getAllPafCounts']);
Route::post('/paf_op_dispense', [PAFApiController::class, 'pafOPDispense']);
Route::post('/mark_non_conformance', [PAFApiController::class, 'markNonConformance']);
Route::get('/get_confirmation_text', [PAFApiController::class, 'getConfirmationText']);
Route::post('/revalidate_non_conformance', [PAFApiController::class, 'revalidateNonConformance']);
Route::post('/check_off_label_reasons', [PAFApiController::class, 'checkOffLabelReasons']);
Route::get('/get_all_paf_report', [PAFApiController::class, 'getAllPafReport']);

// Non conformace controller
Route::get('/fetch_nonconfrules', [NonConformaceRulesApiController::class, 'index']);
Route::post('/create_nonconfrules', [NonConformaceRulesApiController::class, 'store']);
Route::get('/edit_non_conformance_rules/{slug}', [NonConformaceRulesApiController::class, 'editNonConformanceRules']);
Route::post('/update_non_conformance_status', [NonConformaceRulesApiController::class, 'updateNonConformanceStatus']);

// Confiramtion Text controller
Route::get('/fetch_confirmation_texts', [PAFConfirmationTextApiController::class, 'index']);
Route::get('/edit_confirmation_text/{id}', [PAFConfirmationTextApiController::class, 'editConfirmationText']);
Route::post('/create_confirmation_text', [PAFConfirmationTextApiController::class, 'store']);
Route::post('/update_confirmation_text_status', [PAFConfirmationTextApiController::class, 'updateConfirmationTextStatus']);
Route::get('/fetch_confirmation_text_by_type', [PAFConfirmationTextApiController::class, 'fetchConfirmationTextByType']);

// PAF Document Controller
Route::get('/fetch_paf_documents', [PAFDocumentApiController::class, 'index']);
Route::get('/paf_documents/{slug}', [PAFDocumentApiController::class, 'editDocument']);
Route::post('/create_paf_document', [PAFDocumentApiController::class, 'store']);
Route::patch('/paf_documents/{id}', [PAFDocumentApiController::class, 'update']);
Route::delete('/paf_documents/{id}', [PAFDocumentApiController::class, 'delete']);

Route::get('/fetch_chid_documents/{slug}', [PAFDocumentApiController::class, 'fetchChidDocuments']);
Route::get('/fetch_documents/{search?}', [PAFDocumentApiController::class, 'fetchDocuments']);

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
