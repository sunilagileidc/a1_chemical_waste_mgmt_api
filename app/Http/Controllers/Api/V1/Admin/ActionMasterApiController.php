<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActionMaster;
use App\Models\RoleAction;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActionMasterApiController extends Controller
{
    public function index()
    {
        try {
            $actions = ActionMaster::orderBy('id', 'desc')->get();

            return response()->json([
                'status' => 'S',
                'data'   => $actions,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'action_name' => 'required|string|max:250|unique:action_master,action_name',
                'category'    => 'nullable|string|max:250',
                'description' => 'nullable|string|max:500',
                'status'      => 'required|integer',
            ], [
                'action_name.unique' => 'The action name is already taken. Please choose another name.',
            ]);

            $action = ActionMaster::create([
                 ...$validated,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'status'  => 'S',
                'message' => 'Action created successfully',
                'data'    => $action,
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function show($slug)
    {
        try {
            $action = ActionMaster::where('slug', $slug)->first();

            if (! $action) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Action not found',
                ], 404);
            }

            return response()->json([
                'status' => 'S',
                'data'   => $action,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'action_name' => 'required|string|max:250|unique:action_master,action_name,' . $id,
                'category'    => 'nullable|string|max:250',
                'description' => 'nullable|string|max:500',
                'status'      => 'required|integer',
            ], [
                'action_name.unique' => 'The action name is already taken. Please choose another name.',
            ]);

            $action = ActionMaster::findOrFail($id);

            $action->update([
                 ...$validated,
                'updated_by' => Auth::id(),
            ]);

            return response()->json([
                'status'  => 'S',
                'message' => 'Action updated successfully',
                'data'    => $action,
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $action = ActionMaster::findOrFail($id);
            $action->delete();

            return response()->json([
                'status'  => 'S',
                'message' => 'Action deleted successfully',
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateActionMasterStatus(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:action_master,id',
            ]);

            $action = ActionMaster::findOrFail($request->id);

            $action->status = $action->status == 1 ? 0 : 1;
            $action->save();

            return response()->json([
                'status'  => 'S',
                'message' => 'Action status updated successfully.',
                'data'    => [
                    'id'     => $action->id,
                    'status' => $action->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => 'Error updating action status: ' . $e->getMessage(),
            ]);
        }
    }

    public function fetchRoleActions($roleId)
    {
        try {

            $assignedActions = RoleAction::where('role_id', $roleId)
                ->where('status', 1)
                ->pluck('action_id')
                ->toArray();

            $actions = ActionMaster::orderBy('id')
                ->get()
                ->map(function ($action) use ($assignedActions) {
                    return [
                        'id'          => $action->id,
                        'action_name' => $action->action_name,
                        'category'    => $action->category,
                        'description' => $action->description,
                        'checked'     => in_array($action->id, $assignedActions),
                    ];
                });

            return response()->json([
                'status'  => 'S',
                'actions' => $actions,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function storeRoleAction(Request $request)
    {
        try {

            $roleId    = $request->role_id;
            $actionIds = $request->role_access ?? [];

            RoleAction::where('role_id', $roleId)
                ->delete();

            foreach ($actionIds as $actionId) {
                RoleAction::insert([
                    'role_id'    => $roleId,
                    'action_id'  => $actionId,
                    'status'     => 1,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                ]);
            }

            return response()->json([
                'status'  => 'S',
                'message' => 'Role actions updated successfully',
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    public function checkActionPermission(Request $request)
    {
        try {

            $role_id = $request->role_id;

            if (! is_numeric($role_id)) {
                return response()->json([
                    'status'  => "E",
                    'message' => "Invalid Role ID",
                ], 400);
            }

            // Get all active action_ids for role
            $actionIds = RoleAction::where('role_id', $role_id)
                ->where('status', 1)
                ->pluck('action_id');

            if ($actionIds->isEmpty()) {
                return response()->json([
                    'status'      => "S",
                    'permissions' => [],
                    'message'     => "No permissions found",
                ]);
            }

            // Get action names
            $permissions = ActionMaster::whereIn('id', $actionIds)
                ->pluck('action_name');

            return response()->json([
                'status'      => "S",
                'permissions' => $permissions,
                'message'     => "Permissions fetched successfully",
            ]);

        } catch (\Exception $e) {

            \Log::error("Permission Fetch Error: " . $e->getMessage());

            return response()->json([
                'status'  => "E",
                'message' => "Something went wrong while fetching permissions.",
            ], 500);
        }
    }

}
