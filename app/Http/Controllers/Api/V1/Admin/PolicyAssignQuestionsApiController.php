<?php
namespace App\Http\Controllers\api\v1\admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\PolicyAssignedQuestions;
use App\Models\PolicyQuestions;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolicyAssignQuestionsApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1️ List Assigned Questions (By Parent)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        try {

            $request->validate([
                'parent_slug' => 'required|string|exists:policy_questions,slug',
            ]);

            // Fetch Parent
            $parent = PolicyQuestions::where('slug', $request->parent_slug)->firstOrFail();

            $assigned = PolicyAssignedQuestions::where('parent_id', $parent->id)
                ->orderBy('sequence')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'status' => 'S',
                'data'   => $assigned,
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 2️ Store
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'parent_slug' => 'required|string|exists:policy_questions,slug',
                'q_type'      => 'required|string|in:Plain Text,Checkbox',
                'question'    => 'required|string',
                'description' => 'nullable|string|max:2000',
                'sequence'    => 'required|integer|min:1',
                'doc_title'   => 'nullable|string|max:150',
                'doc_link'    => 'nullable|string|max:500',
                'status'      => 'nullable|integer',
                'attach_doc'  => 'nullable|boolean',
            ]);

            // Fetch Parent ID from Slug
            $parent = PolicyQuestions::where('slug', $validated['parent_slug'])->firstOrFail();

            $assigned = PolicyAssignedQuestions::create([
                'parent_id'   => $parent->id,
                'q_type'      => $validated['q_type'],
                'question'    => $validated['question'],
                'description' => $validated['description'] ?? null,
                'sequence'    => $validated['sequence'],
                'attach_doc'  => $validated['attach_doc'],
                'doc_title'   => $validated['doc_title'] ?? null,
                'doc_link'    => $validated['doc_link'] ?? null,
                'status'      => $validated['status'] ?? 1,
                'created_by'  => Auth::id(),
            ]);

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'CREATE',
                referenceId: $assigned->id,
                referenceTable: 'policy_assigned_questions',
                newValues: $assigned->toArray(),
                description: "Assigned question '{$assigned->question}' created"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Assigned Question created successfully',
                'data'    => $assigned,
            ], 201);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    /*
    |--------------------------------------------------------------------------
    | 3️ Show
    |--------------------------------------------------------------------------
    */
    public function show($slug)
    {
        try {

            $assigned = PolicyAssignedQuestions::where('slug', $slug)->first();

            if (! $assigned) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Assigned Question not found',
                ], 404);
            }

            return response()->json([
                'status' => 'S',
                'data'   => $assigned,
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 4️ Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $slug)
    {
        try {

            $validated = $request->validate([
                'q_type'      => 'required|string|in:Plain Text,Checkbox',
                'question'    => 'required|string',
                'description' => 'nullable|string|max:2000',
                'sequence'    => 'required|integer|min:1',
                'doc_title'   => 'nullable|string|max:150',
                'doc_link'    => 'nullable|string|max:500',
                'status'      => 'nullable|integer',
                'attach_doc'  => 'nullable|boolean',
            ]);

            $assigned = PolicyAssignedQuestions::where('slug', $slug)->first();
            $oldData  = $assigned->toArray();
            if (! $assigned) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Assigned Question not found',
                ], 404);
            }

            $assigned->update([
                'q_type'      => $validated['q_type'],
                'question'    => $validated['question'],
                'description' => $validated['description'] ?? null,
                'sequence'    => $validated['sequence'],
                'doc_title'   => ! empty($validated['attach_doc'])
                    ? ($validated['doc_title'] ?? null)
                    : null,
                'doc_link'    => ! empty($validated['attach_doc'])
                    ? ($validated['doc_link'] ?? null)
                    : null,
                'status'      => $validated['status'] ?? $assigned->status,
                'attach_doc'  => $validated['attach_doc'] ?? 0,
                'updated_by'  => Auth::id(),
            ]);

            $newData = $assigned->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'UPDATE',
                referenceId: $assigned->id,
                referenceTable: 'policy_assigned_questions',
                oldValues: $oldData,
                newValues: $newData,
                description: "Assigned question '{$oldData['question']}' updated to '{$assigned->question}'"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Assigned Question updated successfully',
                'data'    => $assigned,
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 5️ Delete
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {

            $assigned = PolicyAssignedQuestions::findOrFail($id);

            $oldData = $assigned->toArray();

            $assigned->delete();

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'policy_assigned_questions',
                oldValues: $oldData,
                description: "Assigned question '{$oldData['question']}' deleted"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Assigned Question deleted successfully',
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | 6️ Toggle Status
    |--------------------------------------------------------------------------
    */
    public function updateAssignPolicyQuestionStatus(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|integer|exists:policy_assigned_questions,id',
            ]);

            $assigned = PolicyAssignedQuestions::findOrFail($request->id);

            $oldStatus = $assigned->status;

            $assigned->status = $assigned->status == 1 ? 0 : 1;
            $assigned->save();

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'UPDATE',
                referenceId: $assigned->id,
                referenceTable: 'policy_assigned_questions',
                oldValues: [
                    'status' => $oldStatus,
                ],
                newValues: [
                    'status' => $assigned->status,
                ],
                changedFields: ['status'],
                description: "Assigned question '{$assigned->question}' status updated"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Assigned Question status updated successfully',
                'data'    => $assigned,
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
