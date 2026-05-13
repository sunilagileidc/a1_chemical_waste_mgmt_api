<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Log;

class MenuApiController extends Controller
{
    public function __construct(Request $request)
    {
        $locale = $request->input('lang');
        if (!in_array($locale, ['ar', 'en'])) {
            $locale = 'en';
        }
        App::setLocale($locale);
    }
    /**
     * @function: to fetch menu details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function index(Request $request)
    {
        try {
            $menus = Menu::orderBy('updated_at', 'desc')->where('status', 1)->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'menu' => $menus]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store menu details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function store(Request $request)
    {
        try {
            if (Menu::where('title', $request->title)->count() > 0) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.menu') . ' ' . $request->title . ', ' . trans('returnmessage.already_exists')]);
            }
            $request['is_header'] = 0;
            $menu = Menu::create($request->all());

            CustomFunctions::updateSlug($menu->id, $request->title, 'menus');

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'menu' => $menu]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to edit menu details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function edit($slug)
    {
        try {
            $menu = Menu::where('slug', $slug)->firstOrFail();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'menu' => $menu]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to update menu details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function update(Request $request, $id)
    {
        try {

            if (Menu::where('title', $request->title)->where('id', '!=', $id)->count() > 0) {
                return response()->json(['status' => 'E', 'message' => trans('returnmessage.menu') . $request->title . trans('returnmessage.already_exists')]);
            }
            $menu = Menu::findOrFail($id);
            $menu->update($request->all());

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.updatedsuccessfully'), 'menu' => $menu]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to delete menu details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function destroy($id)
    {
        try
        {
            Menu::destroy($id);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_delete')]);
        }
    }

    /**
     * @function: to fetch menu tree details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function menutree(Request $request)
    {
        try {
            // $locale = $request->input('lang');
            // if (!in_array($locale, ['ar', 'en'])) {
            //     $locale = 'en';
            // }
            // App::setLocale($locale);

            $role_data = Role::where('id', $request->role)->first();
            $roleid = $role_data->id;
            $menus = Menu::join('role_menu', 'menu_id', 'id')
                ->where('role_id', $roleid)
                ->where('parent_id', 0)
                ->with('child', function ($query) use ($roleid) {
                    $query->join('role_menu', 'menu_id', 'id')
                        ->where('role_menu.role_id', $roleid)->orderBy('seq', 'asc');
                })
                ->orderBy('seq', 'asc')
                ->distinct()
                ->get();
            $processedmenu = collect();
            foreach ($menus as $menu) {
                $menu['title'] = trans('menu.' . $menu->title);
                $menu['classactive'] = false;
                if (count($menu->child) == 0) {
                    unset($menu->child);
                } else {
                    foreach ($menu->child as $subchild) {
                        $subchild['title'] = trans('menu.' . $subchild->title);
                        if (count($subchild->child) == 0) {
                            unset($subchild->child);
                        } else {
                            foreach ($subchild->child as $lowchild) {
                                $lowchild['title'] = trans('menu.' . $lowchild->title);
                                if (count($lowchild->child) == 0) {
                                    unset($lowchild->child);
                                }
                            }
                        }
                    }
                }
                $processedmenu->add($menu);
            }
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'menu' => $menus]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch menu role details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function rolemenu()
    {
        try {
            $menus = Menu::Where('parent_id', 0)
                ->where('is_header', 0)
                ->with('children')
                ->select('id', 'title as name')
                ->orderBy('seq', 'asc')
                ->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.menu_added_success'), 'menu' => $menus]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch parent details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function parentMenus()
    {
        try {
            $parentmenus = Menu::where('is_header', 0)->where('parent_id', '<', 1)->orderBy('title')->get();
            return response()->json(['status' => 'S', 'mesage' => trans('returnmessage.dataretreived'), 'parentmenu' => $parentmenus]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch menu access details.
     *
     * @author: Raghavendra kumar
     *
     * @created-on: 04 Jan, 2026
     *
     * @updated-on: N/A
     */
    public function getmenuaccess($roleid)
    {
        try {
            $selectedmenu = RoleMenu::join('menus', 'menus.id', 'menu_id')
                ->where('role_id', $roleid)
                ->pluck('menu_id')->toArray();
            return response()->json(['status' => 'S', 'mesage' => trans('returnmessage.dataretreived'), 'selected_menu' => $selectedmenu]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

    /**
     * @function: to store menu access details.
     *
     * @author: Suprith S
     *
     * @created-on: 3 Dec, 2022
     *
     * @updated-on: N/A
     */
    public function storemenuaccess(Request $request)
    {
        try {
            $data = array();
            Log::info($request);
            $parentids = array();
            $currenttime = date('Y-m-d h:i:s');
            RoleMenu::where('role_id', $request->role_id)->delete();
            for ($i = 0; $i < count($request->role_access); $i++) {
                $data[] = ['role_id' => $request->role_id, 'menu_id' => $request->role_access[$i], 'created_at' => $currenttime, 'updated_at' => $currenttime];
                $parent_id = Menu::where('id', $request->role_access[$i])->pluck('parent_id')->first();
                if ($parent_id > 0) {
                    $parentids[] = $parent_id;
                }
            }
            $result = array_unique($parentids);
            $is_3rdlvl_exists = 'N';

            foreach ($result as $parent_menu_id) {

                $data[] = ['role_id' => $request->role_id, 'menu_id' => $parent_menu_id, 'created_at' => $currenttime, 'updated_at' => $currenttime];
                $main_parent_id = Menu::where('id', $parent_menu_id)->pluck('parent_id')->first();
                if ($main_parent_id > 0) {
                    $is_3rdlvl_exists = 'Y';
                    $mainparentids[] = $main_parent_id;
                }
            }

            if ($is_3rdlvl_exists == 'Y') {
                $main_result = array_unique($mainparentids);
                foreach ($main_result as $main_parent_menu_id) {
                    $data[] = ['role_id' => $request->role_id, 'menu_id' => $main_parent_menu_id, 'created_at' => $currenttime, 'updated_at' => $currenttime];
                }
            }

            RoleMenu::insert($data);
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.saved_success')]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'errordata' => $e->getmessage()]);
        }
    }

}
