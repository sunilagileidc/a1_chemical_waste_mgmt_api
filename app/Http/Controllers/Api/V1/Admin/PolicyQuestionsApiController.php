<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Models\Drugs;
use App\Models\PolicyAssignedQuestions;
use App\Models\PolicyQuestions;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PolicyQuestionsApiController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | 1 List All Policy Questions
    |--------------------------------------------------------------------------
    */
    public function index()
    {
        try {

            $policies = PolicyQuestions::orderBy('sequence')
                ->orderBy('id', 'desc')
                ->get();

            // Group ref_values by ref_type
            $grouped = $policies->groupBy('ref_type');

            $refData = [];

            foreach ($grouped as $type => $items) {

                $ids = $items->pluck('ref_value')->unique()->toArray();

                switch ($type) {

                    case 'Drugs':
                        $refData[$type] = \DB::table('drugs')
                            ->whereIn('id', $ids)
                            ->pluck('drug_name', 'id');
                        break;

                        // case 'Pharmacist':
                        //     $refData[$type] = \DB::table('pharmacists')
                        //         ->whereIn('id', $ids)
                        //         ->pluck('name', 'id');
                        //     break;

                }
            }

            // Attach name dynamically
            $policies->transform(function ($policy) use ($refData) {

                $policy->ref_name = $refData[$policy->ref_type][$policy->ref_value] ?? null;

                return $policy;
            });

            return response()->json([
                'status' => 'S',
                'data'   => $policies,
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
    | 2 Store (Create)
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {

            $validated = $request->validate([
                'title'       => 'required|string|max:250',
                'description' => 'nullable|string|max:2000',
                'linked_to'   => 'nullable|string|max:250',
                'ref_type'    => 'required|string|max:250',
                'ref_value'   => 'required',
                'sequence'    => 'required|integer|min:1',
                'status'      => 'nullable|integer',
            ]);

            $policy = PolicyQuestions::create([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'linked_to'   => $validated['linked_to'] ?? null,
                'ref_type'    => $validated['ref_type'],
                'ref_value'   => $validated['ref_value'],
                'sequence'    => $validated['sequence'],
                'status'      => $validated['status'] ?? 1,
                'created_by'  => Auth::id(),
            ]);

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'CREATE',
                referenceId: $policy->id,
                referenceTable: 'policy_questions',
                newValues: $policy->toArray(),
                description: "Policy question '{$policy->title}' created"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Policy Question created successfully',
                'data'    => $policy,
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
    | 3 Show By Slug (Edit Fetch)
    |--------------------------------------------------------------------------
    */
    public function show($slug)
    {
        try {

            $policy = PolicyQuestions::where('slug', $slug)->first();

            if (! $policy) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Policy Question not found',
                ], 404);
            }

            return response()->json([
                'status' => 'S',
                'data'   => $policy,
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
    | 4 Update
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        try {

            $validated = $request->validate([
                'title'       => 'required|string|max:250',
                'description' => 'nullable|string|max:2000',
                'linked_to'   => 'nullable|string|max:250',
                'ref_type'    => 'required|string|max:250',
                'ref_value'   => 'required',
                'sequence'    => 'required|integer|min:1',
                'status'      => 'nullable|integer',
            ]);

            $policy = PolicyQuestions::findOrFail($id);

            $oldData = $policy->toArray();

            $policy->update([
                'title'       => $validated['title'],
                'description' => $validated['description'] ?? null,
                'linked_to'   => $validated['linked_to'] ?? null,
                'ref_type'    => $validated['ref_type'],
                'ref_value'   => $validated['ref_value'],
                'sequence'    => $validated['sequence'],
                'status'      => $validated['status'] ?? $policy->status,
                'updated_by'  => Auth::id(),
            ]);

            $newData = $policy->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'UPDATE',
                referenceId: $policy->id,
                referenceTable: 'policy_questions',
                oldValues: $oldData,
                newValues: $newData,
                description: "Policy question '{$policy->title}' updated"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Policy Question updated successfully',
                'data'    => $policy,
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
    | 5 Delete
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        try {

            $policy = PolicyQuestions::findOrFail($id);

            $title = $policy->title;

            $policy->delete();

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'DELETE',
                referenceId: $id,
                referenceTable: 'policy_questions',
                description: "Policy question '{$title}' deleted"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Policy Question deleted successfully',
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
    | 6 Toggle Status
    |--------------------------------------------------------------------------
    */
    public function updatePolicyQuestionStatus(Request $request)
    {
        try {

            $request->validate([
                'id' => 'required|integer|exists:policy_questions,id',
            ]);

            $policy = PolicyQuestions::findOrFail($request->id);

            $policy->status = $policy->status == 1 ? 0 : 1;
            $policy->save();

            $statusText = $policy->status == 1 ? 'Active' : 'Inactive';

            CustomFunctions::audit(
                module: 'Policy Questions',
                action: 'UPDATE STATUS',
                referenceId: $policy->id,
                referenceTable: 'policy_questions',
                newValues: ['status' => $policy->status],
                description: "Policy question '{$policy->title}' status changed to {$statusText}"
            );

            return response()->json([
                'status'  => 'S',
                'message' => 'Policy status updated successfully',
                'data'    => [
                    'id'     => $policy->id,
                    'status' => $policy->status,
                ],
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => 'Error updating policy status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /*
|--------------------------------------------------------------------------
| 7 Fetch Policy Questions For Registration
|--------------------------------------------------------------------------
*/
    public function fetchRegPolicyQuestions(Request $request)
    {

        try {

            $request->validate([
                'type'          => 'required|string', // Prescriber / Pharmacist
                'medications'   => 'required|array|min:1',
                'medications.*' => 'integer',
            ]);

            $type        = $request->type;        // Prescriber or Pharmacist
            $medications = $request->medications; // selected medication ids

            $policies = PolicyQuestions::where('linked_to', $type)
                ->where('ref_type', 'Drugs')
                ->whereIn('ref_value', $medications)
                ->where('status', 1)
                ->orderBy('sequence')
                ->get();

            $drugList = Drugs::whereIn('id', $medications)
                ->get(['drug_name', 'id', 'validity']);

            $result = [];

            foreach ($policies as $policy) {

                $assignedQuestions = PolicyAssignedQuestions::where('parent_id', $policy->id)
                    ->where('status', 1)
                    ->orderBy('sequence')
                    ->get([
                        'id',
                        'question',
                        'q_type',
                        'description',
                        'attach_doc',
                        'doc_title',
                        'doc_link',
                    ]);

                $result[] = [
                    'id'        => $policy->ref_value,
                    'title'     => $policy->title,
                    'questions' => $assignedQuestions,
                ];
            }

            return response()->json([
                'status'      => 'S',
                'medications' => $result,
                'drug_list'   => $drugList,
            ], 200);

        } catch (Exception $e) {

            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

}
