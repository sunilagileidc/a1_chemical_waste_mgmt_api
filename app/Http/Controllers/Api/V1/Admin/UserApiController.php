<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\UserRegistrationMail;
use App\Models\Careers;
use App\Models\Category;
use App\Models\CustomerNewsletter;
use App\Models\EmailTemplate;
use App\Models\Events;
use App\Models\HeaderAnswer;
use App\Models\Products;
use App\Models\PromotionsOffers;
use App\Models\Role;
use App\Models\ServiceSlotBooking;
use App\Models\ServicesSlots;
use App\Models\Stores;
use App\Models\Testimonials;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Log;
use Mail;

class UserApiController extends Controller
{
    /**
     * @function: to fetch user details.
     *
     * @author: Rohith R
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchUser(Request $request)
    {
        try {
            // $admin_role = Role::where('rolename', 'Admin')->first();
            // where('role_id', $admin_role->id)->
            $usersdata = User::with('role')->orderBy('updated_at', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to save user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function saveUser(Request $request)
    {
        // Log::info($request);
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'lastname' => 'required',
            'email' => 'required',
        ]);
        try {
            $currenttime = date('Y-m-d h:i:s');
            if ($validator->fails()) {
                return response()->json(['status' => 'E', 'message' => $validator->errors()->all()]);
            } else {
                $userexists = User::where('email', $request->email)->where('id', '!=', $request->id)->first();
                if ($userexists) {
                    return response()->json(['status' => 'E', 'message' => trans('returnmessage.email_already_exists')]);
                }
                if ($request->password == null) {
                    $generated_password = rand(100000, 999999);
                    $password = Hash::make($generated_password);
                } else {
                    $password = Hash::make($request->password);
                }

                if ($request->id > 0) {
                    DB::beginTransaction();
                    $users = User::where('id', $request->id)
                        ->update([
                            'salutation' => $request->salutation,
                            'name' => Str::ucfirst($request->name),
                            'lastname' => Str::ucfirst($request->lastname),
                            'gender' => $request->gender,
                            'dob' => $request->dob,
                            'email' => $request->email,
                            'mobile' => $request->mobile,
                            'phone' => $request->phone,
                            'mobile_code' => $request->mobile_code,
                            'role_id' => $request->role_id,
                            'address' => $request->address,
                            'country' => $request->country,
                            'state' => $request->state,
                            'city' => $request->city,
                            'postcode' => $request->postcode,
                            'description' => $request->description,
                            'image_url' => $request->image_url,
                            'updated_at' => $currenttime,
                        ]);

                    $users = User::with('role')->where('id', $request->id)->first();
                    DB::commit();

                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'userdata' => $users]);
                } else {
                    DB::beginTransaction();
                    $users = User::create([
                        'salutation' => $request->salutation,
                        'name' => Str::ucfirst($request->name),
                        'lastname' => Str::ucfirst($request->lastname),
                        'email' => $request->email,
                        'gender' => $request->gender,
                        // 'dob' => $request->dob,
                        'store_id' => $request->store_id,
                        'password' => $password,
                        'mobile' => $request->mobile,
                        'phone' => $request->phone,
                        'mobile_code' => $request->mobile_code,
                        'role_id' => $request->role_id,
                        'address' => $request->address,
                        'country' => $request->country,
                        'state' => $request->state,
                        'city' => $request->city,
                        'postcode' => $request->postcode,
                        'description' => $request->description,
                        'image_url' => $request->image_url,
                        'created_at' => $currenttime,
                        'updated_at' => $currenttime,
                    ]);
                    $rolename = Role::where('id', $request->role_id)->value('rolename');

                    if ($rolename == 'StoreAdmin') {
                        $store_name = Stores::where('header_id', $request->store_id)->first(['name', 'mall_name']);
                        $mall_name = Stores::where('header_id', $store_name->mall_name)->value('name');
                        $emailTemplate = EmailTemplate::where('template_name', 'StoreAdmin Credentials')->first(); // die();
                        if (isset($emailTemplate)) {
                            $actionText = null;
                            $actionUrl = null;
                            $userdata = ['name' => $request->name, 'password' => $generated_password, 'storename' => $store_name->name, 'email' => $request->email, 'mall_name' => $mall_name, 'website' => config('mail.cms_app_url')];
                            $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                            $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                            $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                            Mail::to($request->email)->send(new UserRegistrationMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
                        }
                    }
                    DB::commit();
                    return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'userdata' => $users]);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update user details.
     *
     * @author: Santhosha G
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function updateUserStatus(request $request)
    {
        try {
            $user = User::where('id', $request->id)->first();
            if ($user->status == 1) {
                $status = User::where('id', $request->id)->update(['status' => 0]);
            } else {
                $status = User::where('id', $request->id)->update(['status' => 1]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function updateBlacklistStatus(request $request)
    {
        try {
            //zero for not blacklisted, one for blacklisted
            $user = User::where('id', $request->id)->first();
            if ($user->is_blacklisted == 0) {
                $status = User::where('id', $request->id)->update(['status' => 0, 'is_blacklisted' => 1, 'blacklist_comments' => $request->blacklist_reason]);
            } else {
                $status = User::where('id', $request->id)->update(['status' => 1, 'is_blacklisted' => 0, 'blacklist_comments' => $request->blacklist_reason]);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch user details using Slug.
     *
     * @author: Santhosha G
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function fetchUserBySlug($slug)
    {
        try {
            $user = User::with('role')->where('slug', $slug)->first();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function fetchUserDataBySlug($slug)
    {
        try {
            $user = User::with('role', 'country', 'state', 'city')->where('slug', $slug)->first();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to send credentials to mail.
     *
     * @author: Santhosha G
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function sendCredentials(Request $request)
    {
        try {
            $generated_password = rand(100000, 999999);
            $password = Hash::make($generated_password);

            $user = User::where('email', $request->email)
                ->update([
                    'password' => $password,
                ]);

            $emailTemplate = EmailTemplate::where('template_name', 'Send Credentials')->first();
            if (isset($emailTemplate)) {
                $actionText = null;
                $actionUrl = null;
                $userdata = ['name' => $request->name, 'password' => $generated_password];
                $parsedSubject = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                $parsedContent = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);
                Mail::to($request->email)->send(new UserRegistrationMail($parsedSubject, $parsedContent, $paresedSignature, $actionText, $actionUrl));
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.credentials_sent')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing')]);
        }
    }

    public function fetchTradieUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Tradie')->first();

            $usersdata = User::where('role_id', $tradie_role->id)->where('is_blacklisted', 0)->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchBlacklistTradieUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Tradie')->first();

            $usersdata = User::where('role_id', $tradie_role->id)->where('is_blacklisted', 1)->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchPrincipalUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Principal')->first();

            $usersdata = User::where('role_id', $tradie_role->id)->orderBy('id', 'desc')->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchTradieAllUser(Request $request)
    {
        try {
            $tradie_role = Role::where('rolename', 'Tradie')->first();

            $different_user_id = HeaderAnswer::distinct('user_id')->where('status', '!=', 'Draft')
                ->where('status', '!=', null)->where('status', '!=', 'Pending')->pluck('user_id');

            $usersdata = User::with('headerApproved')->whereIn('id', $different_user_id)
                ->where('role_id', $tradie_role->id)
                ->orderBy('id', 'desc')
                ->get();

            // Log::info($usersdata);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'usersdata' => $usersdata]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
    public function fetchDashboardSuperUser($user_id)
    {
        $role_id = User::where('id', $user_id)->value('role_id');
        $rolename = Role::where('id', $role_id)->value('rolename');
        try {
            $count_dashboard = [];
            $user_array = [
                'name' => 'Users',
                'icon' => 'mdi mdi-account-multiple',
                'color' => 'success',
                'status' => [
                    [
                        'count' => User::where('status', 1)->count() - 1,
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => User::where('status', 0)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $events_array = [
                'name' => 'Events',
                'icon' => 'mdi mdi-calendar-check',
                'color' => 'warning',
                'status' => [
                    [
                        'count' => Events::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Events::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Events::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $promotion_array = [
                'name' => 'Promotions',
                'icon' => 'mdi mdi-ticket-percent',
                'color' => 'primary',
                'status' => [
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_array = [
                'name' => 'Products',
                'icon' => 'mdi mdi-view-module',
                'color' => 'error',
                'status' => [
                    [
                        'count' => Products::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Products::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Products::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $category_array = [
                'name' => 'Categories',
                'icon' => 'mdi mdi-apps',
                'color' => 'secondary',
                'status' => [
                    [
                        'count' => Category::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Category::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Category::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $career_array = [
                'name' => 'Careers',
                'icon' => 'mdi mdi-briefcase',
                'color' => '#b3d4fc',
                'status' => [
                    [
                        'count' => Careers::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Careers::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Careers::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $stores_array = [
                'name' => 'Stores',
                'icon' => 'mdi mdi-store-clock',
                'color' => '#f7b924',
                'status' => [
                    [
                        'count' => Stores::where('approval_status', 'Approved')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Stores::where('approval_status', 'In Review')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],

                    [
                        'count' => Stores::where('approval_status', 'Rejected')->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $testimonials_array = [
                'name' => 'Testimonials',
                'icon' => 'mdi mdi-message-text-fast-outline',
                'color' => 'success',
                'status' => [
                    [
                        'count' => Testimonials::where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => Testimonials::where('status', 0)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $total_slots = (ServicesSlots::sum('slots') - ServiceSlotBooking::sum('slots'));
            $service_bookings_array = [
                'name' => 'ServiceBookings',
                'icon' => 'mdi mdi-notebook-check',
                'color' => 'error',
                'status' => [
                    [
                        'count' => ServiceSlotBooking::sum('slots'),
                        'color' => 'success',
                        'status_name' => 'Claims',
                    ],
                    [
                        'count' => $total_slots,
                        'color' => 'warning',
                        'status_name' => 'Available',

                    ],
                ],
            ];
            $newsletter_subscriptions = [
                'name' => 'NewsletterSubscriptions',
                'icon' => 'mdi mdi-email-newsletter',
                'color' => 'error',
                'status' => [
                    [
                        'count' => CustomerNewsletter::count(),
                        'color' => 'success',
                        'status_name' => 'Subscriptions',
                    ],
                ],
            ];
            array_push($count_dashboard, $user_array, $events_array, $promotion_array, $products_array, $category_array, $career_array, $stores_array, $service_bookings_array, $newsletter_subscriptions);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'count_dashboard' => $count_dashboard]);
            // Log::info($count_dashboard);

            // // $user_active = $user->where('status', 1)->count();
            // // $user_inactive = $user->where('status', 0)->count();

            // // $count_dashboard = array(
            // //     0 => array(
            // //         'count' => $user_active,
            // //     ),
            // //     1 => array(
            // //         'count' => $user_inactive,
            // //     ),
            // // );

            // // Log::info('count d  ' . $count_dashboard);

            // die();

            //zero for not blacklisted, one for blacklisted
            // $activeuser = User::where('status', 1)->count();
            // $inactiveuser = User::where('status', 0)->count();
            // $irevents = Events::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apevents = Events::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $reevents = Events::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $irpromo = PromotionsOffers::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $appromo = PromotionsOffers::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $repromo = PromotionsOffers::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $irprod = Products::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $approd = Products::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $reprod = Products::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $ircat = Category::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apcat = Category::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $recat = Category::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $ircar = Careers::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apcar = Careers::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $recar = Careers::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $irstore = Stores::where('approval_status', 'In Review')->where('lang', 'en')->count();
            // $apstore = Stores::where('approval_status', 'Approved')->where('lang', 'en')->count();
            // $re_store = Stores::where('approval_status', 'Rejected')->where('lang', 'en')->count();
            // $aptesti = Testimonials::where('status', 1)->count();
            // $retesti = Testimonials::where('status', 0)->count();

            // return response()->json(['status' => 'S',
            //     'activeuser' => $activeuser,
            //     'inactiveuser' => $inactiveuser,
            //     'irevents' => $irevents,
            //     'apevents' => $apevents,
            //     'reevents' => $reevents,
            //     'irpromo' => $irpromo,
            //     'appromo' => $appromo,
            //     'repromo' => $repromo,
            //     'irprod' => $irprod,
            //     'approd' => $approd,
            //     'reprod' => $reprod,
            //     'ircat' => $ircat,
            //     'apcat' => $apcat,
            //     'recat' => $recat,
            //     'ircar' => $ircar,
            //     'apcar' => $apcar,
            //     'recar' => $recar,
            //     'irstore' => $irstore,
            //     'apstore' => $apstore,
            //     're_store' => $re_store,
            //     'aptesti' => $aptesti,
            //     'retesti' => $retesti,
            //     'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function fetchDashboardMallAdmin($user_id)
    {
        $role_id = User::where('id', $user_id)->value('role_id');
        $rolename = Role::where('id', $role_id)->value('rolename');
        $mall_id = User::where('id', $user_id)->value('store_id');
        $store_id = Stores::where('mall_name', $mall_id)->where('lang', 'en')->pluck('id');
        try {
            $count_dashboard = [];
            $user_array = [
                'name' => 'Users',
                'icon' => 'mdi mdi-account-multiple',
                'color' => 'success',
                'status' => [
                    [
                        'count' => User::where('status', 1)->whereIn('store_id', $store_id)->count(),
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => User::where('status', 0)->whereIn('store_id', $store_id)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $events_array = [
                'name' => 'Events',
                'icon' => 'mdi mdi-calendar-check',
                'color' => 'warning',
                'status' => [
                    [
                        'count' => Events::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Events::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Events::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $promotion_array = [
                'name' => 'Promotions',
                'icon' => 'mdi mdi-ticket-percent',
                'color' => 'primary',
                'status' => [
                    [
                        'count' => PromotionsOffers::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_array = [
                'name' => 'Products',
                'icon' => 'mdi mdi-view-module',
                'color' => 'error',
                'status' => [
                    [
                        'count' => Products::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Products::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Products::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $category_array = [
                'name' => 'Categories',
                'icon' => 'mdi mdi-apps',
                'color' => 'pink',
                'status' => [
                    [
                        'count' => Category::where('approval_status', 'In Review')->where('store_id', $mall_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Category::where('approval_status', 'Approved')->where('store_id', $mall_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Category::where('approval_status', 'Rejected')->where('store_id', $mall_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $career_array = [
                'name' => 'Careers',
                'icon' => 'mdi mdi-briefcase',
                'color' => 'purple',
                'status' => [
                    [
                        'count' => Careers::where('approval_status', 'In Review')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Careers::where('approval_status', 'Approved')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Careers::where('approval_status', 'Rejected')->whereIn('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $stores_array = [
                'name' => 'Stores',
                'icon' => 'mdi mdi-store-clock',
                'color' => 'lime',
                'status' => [
                    [
                        'count' => Stores::where('approval_status', 'In Review')->whereIn('id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Stores::where('approval_status', 'Approved')->whereIn('id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Stores::where('approval_status', 'Rejected')->whereIn('id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $testimonials_array = [
                'name' => 'Testimonials',
                'icon' => 'mdi mdi-message-text-fast-outline',
                'color' => 'success',
                'status' => [
                    [
                        'count' => Testimonials::where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Active',

                    ],
                    [
                        'count' => Testimonials::where('status', 0)->count(),
                        'color' => 'warning',
                        'status_name' => 'Inactive',
                    ],
                ],
            ];
            $total_slots = (ServicesSlots::sum('slots') - ServiceSlotBooking::sum('slots'));
            $service_bookings_array = [
                'name' => 'ServiceBookings',
                'icon' => 'mdi mdi-notebook-check',
                'color' => 'error',
                'status' => [
                    [
                        'count' => ServiceSlotBooking::sum('slots'),
                        'color' => 'success',
                        'status_name' => 'Claims',
                    ],
                    [
                        'count' => $total_slots,
                        'color' => 'warning',
                        'status_name' => 'Available',

                    ],
                ],
            ];
            $newsletter_subscriptions = [
                'name' => 'NewsletterSubscriptions',
                'icon' => 'mdi mdi-email-newsletter',
                'color' => 'error',
                'status' => [
                    [
                        'count' => CustomerNewsletter::count(),
                        'color' => 'success',
                        'status_name' => 'Subscriptions',
                    ],
                ],
            ];

            array_push($count_dashboard, $user_array, $events_array, $promotion_array, $products_array, $category_array, $career_array, $stores_array, $service_bookings_array, $newsletter_subscriptions);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'count_dashboard' => $count_dashboard]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    public function fetchDashboardStoreAdmin($user_id)
    {
        $role_id = User::where('id', $user_id)->value('role_id');
        $rolename = Role::where('id', $role_id)->value('rolename');
        $store_id = User::where('id', $user_id)->value('store_id');
        try {
            $count_dashboard = [];
            $events_array = [
                'name' => 'Events',
                'icon' => 'mdi mdi-calendar-check',
                'color' => 'warning',
                'status' => [
                    [
                        'count' => Events::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Events::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Events::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $promotion_array = [
                'name' => 'Promotions',
                'icon' => 'mdi mdi-ticket-percent',
                'color' => 'primary',
                'status' => [
                    [
                        'count' => PromotionsOffers::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => PromotionsOffers::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_array = [
                'name' => 'Products',
                'icon' => 'mdi mdi-view-module',
                'color' => 'error',
                'status' => [
                    [
                        'count' => Products::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Products::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Products::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];

            $career_array = [
                'name' => 'Careers',
                'icon' => 'mdi mdi-briefcase',
                'color' => 'purple',
                'status' => [
                    [
                        'count' => Careers::where('approval_status', 'In Review')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'warning',
                        'status_name' => 'In Review',

                    ],
                    [
                        'count' => Careers::where('approval_status', 'Approved')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'success',
                        'status_name' => 'Approved',
                    ],
                    [
                        'count' => Careers::where('approval_status', 'Rejected')->where('store_id', $store_id)->where('lang', 'en')->where('status', 1)->count(),
                        'color' => 'error',
                        'status_name' => 'Rejected',
                    ],
                ],
            ];
            $products_id = Products::where('store_id', $store_id)->distinct('header_id')->pluck('header_id');
            $slots_id = ServicesSlots::whereIn('service_id', $products_id)->pluck('id');
            $total_slots = (ServicesSlots::whereIn('service_id', $products_id)->sum('slots') - ServiceSlotBooking::whereIn('slots_id', $slots_id)->sum('slots'));
            $service_bookings_array = [
                'name' => 'ServiceBookings',
                'icon' => 'mdi mdi-notebook-check',
                'color' => 'error',
                'status' => [
                    [
                        'count' => ServiceSlotBooking::whereIn('slots_id', $slots_id)->sum('slots'),
                        'color' => 'success',
                        'status_name' => 'Claims',
                    ],
                    [
                        'count' => $total_slots,
                        'color' => 'warning',
                        'status_name' => 'Available',

                    ],
                ],
            ];

            array_push($count_dashboard, $events_array, $promotion_array, $products_array, $career_array, $service_bookings_array);

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success'), 'count_dashboard' => $count_dashboard]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }
}
