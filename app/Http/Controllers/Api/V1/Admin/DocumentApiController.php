<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
use App\Models\Document;
use App\Models\PAFDocument;
use App\Models\User;
use App\Models\EmailTemplate;
use App\Models\PharmacistMedication;
use App\Models\PrescriberMedication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Log;

class DocumentApiController extends Controller
{
    /**
     * @function: to fetch documents data.
     *
     * @author: Santhosha G
     *
     * @created-on: 11 Feb 2026
     *
     * @updated-on: 11 Feb 2026
     */
    public function index()
    {
        try {
            $documents = Document::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('documents')
                    ->groupBy(DB::raw('COALESCE(parent_id, id)'));
            })
                ->orderBy('id', 'desc')
                ->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'documents' => $documents]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch child documets data.
     *
     * @author: Santhosha G
     *
     * @created-on: 13 Mar 2026
     *
     * @updated-on: 13 Mar 2026
     */
    public function fetchChidDocuments($slug)
    {
        try {
            $document = Document::where('slug', $slug)->firstOrFail();

            // determine parent id
            $parentId = $document->parent_id ?? $document->id;

            // fetch all versions except the current one
            $documents = Document::where(function ($query) use ($parentId) {
                $query->where('id', $parentId)
                    ->orWhere('parent_id', $parentId);
            })
                ->where('id', '!=', $document->id) // exclude current slug record
                ->orderBy('id', 'asc')
                ->get();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'documents' => $documents]);
        } catch (\Exception $e) {
            Log::info($e);

            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to fetch full documents details.
     *
     * @author: Santhosha G
     *
     * @created-on: 11 Mar 2026
     *
     * @updated-on: 12 Mar 2026
     */
    public function fetchDocumentDetails()
    {
        try {

            $documents = Document::with('drug')->get();

            $result = [];

            foreach ($documents->groupBy('category') as $category => $docs) {

                if ($category === 'Drugs') {

                    $result[$category] = $docs->groupBy('drug_id')->map(function ($drugDocs, $drugId) {

                        $drug = $drugDocs->first()->drug;

                        return [
                            'drug_id'   => $drugId,
                            'drug_name' => $drug ? $drug->drug_name : null,
                            'documents' => $drugDocs->values(),
                        ];

                    })->values();

                } else {

                    $result[$category] = $docs->values();

                }
            }

            return response()->json([
                'status'    => 'S',
                'message'   => trans('returnmessage.dataretreived'),
                'documents' => $result,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);

        }
    }

    /**
     * @function: to fetch full documents details.
     *
     * @author: Santhosha G
     *
     * @created-on: 17 Mar 2026
     *
     * @updated-on: 17 Mar 2026
     */
    public function fetchLatestDocumentDetails()
    {
        try {

            $old_documents = Document::with('drug')->where('is_training_document', 1)->get();
            $paf_documents = PAFDocument::with('drug')->where('is_training_document', 1)->get();

            $documents = $old_documents->merge($paf_documents)->values();
            // Step 1: Group by parent/root
            $grouped = $documents->groupBy(function ($doc) {
                return $doc->parent_id ?? $doc->id;
            });

            // Step 2: Get latest version from each group
            $latestDocs = $grouped->map(function ($docs) {
                return $docs->sortByDesc('id')->first(); // or created_at
            })->values();

            // Step 3: Maintain your structure
            $result = [];

            foreach ($latestDocs->groupBy('category') as $category => $docs) {

                if ($category === 'Drugs') {

                    $result[$category] = $docs->groupBy('drug_id')->map(function ($drugDocs, $drugId) {

                        $drug = $drugDocs->first()->drug;

                        return [
                            'drug_id'   => $drugId,
                            'drug_name' => $drug ? $drug->drug_name : null,
                            'documents' => $drugDocs->values(),
                        ];
                    })->values();
                } else {
                    $result[$category] = $docs->values();
                }
            }
            return response()->json([
                'status'    => 'S',
                'message'   => trans('returnmessage.dataretreived'),
                'documents' => $result,
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to Upload documents data.
     *
     * @author: Santhosha G
     *
     * @created-on: 12 Feb 2026
     *
     * @updated-on: 12 Feb 2026
     */
    public function uploadFile(Request $request)
    {
        try {
            $base64 = $request->image;

            $data     = explode(',', $base64);
            $fileData = base64_decode($data[1]);

            $folder = $request->folder;

            $filename = $request->filename . '_' . time() . '.' . $request->extension;

            Storage::disk('public')->put("$folder/$filename", $fileData);

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.document_uploaded_success'), 'path' => "$folder/$filename"]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to Upload documents data.
     *
     * @author: Santhosha G
     *
     * @created-on: 11 Feb 2026
     *
     * @updated-on: 11 Feb 2026
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'title'                => 'required|string|max:255',
                'category'             => 'required|string|max:100',
                // 'drug' => 'required',
                'group'                => 'nullable|string',
                'is_re_registration'   => 'integer',
                'is_training_document' => 'integer',
                'download_alert'       => 'integer',
                'description'          => 'nullable|string',
                'sequence'             => 'nullable|integer',
                // file object validation
                'file'                 => 'required|array',
                'file.file_name'       => 'required|string',
                'file.file_path'       => 'required|string',
                'file.file_type'       => 'required|string',
                'file.file_size'       => 'required|integer',
                'file.mime'            => 'required|string',
            ]);
            DB::beginTransaction();
            $authUserId = Auth::id();

            $document = Document::create([
                'title'                => $request->title,
                'description'          => $request->description,
                'category'             => $request->category,
                'group'                => $request->group,
                'is_re_registration'   => $request->is_re_registration,
                'is_training_document' => $request->is_training_document,
                'download_alert'       => $request->download_alert,
                'drug_id'              => $request->drug_id,
                'sequence'             => $request->sequence,
                'file_name'            => $request->file['file_name'],
                'file_path'            => $request->file['file_path'],
                'file_type'            => $request->file['file_type'], // extension (xlsx)
                'file_size'            => $request->file['file_size'], // bytes
                'mime'                 => $request->file['mime'],
                'created_by'           => $authUserId,
                'updated_by'           => $authUserId,
            ]);

            CustomFunctions::audit(
                module: 'Documents',
                action: 'CREATE',
                referenceId: $document->id,
                referenceTable: 'documents',
                newValues: $document->toArray(),
                description: "New document '{$document->title}' created under category '{$document->category}'" . ($document->group ? " (Group: {$document->group})" : "") . ". File: {$document->file_name} ({$document->file_type})"
            );
            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'data' => $document]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to updating the documents data.
     *
     * @author: Santhosha G
     *
     * @created-on: 12 Feb 2026
     *
     * @updated-on: 13 Mar 2026
     */
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'title'                => 'required|string|max:255',
                'category'             => 'required|string|max:100',
                'description'          => 'nullable|string',
                'group'                => 'nullable|string',
                'is_re_registration'   => 'integer',
                'is_training_document' => 'integer',
                'download_alert'       => 'integer',
                'sequence'             => 'nullable|integer',
                'file'                 => 'nullable|array',
                'file.file_name'       => 'nullable|string',
                'file.file_path'       => 'nullable|string',
                'file.file_type'       => 'nullable|string',
                'file.file_size'       => 'nullable|integer',
                'file.mime'            => 'nullable|string',
            ]);
            DB::beginTransaction();
            $authUserId = Auth::id();

            $document = Document::findOrFail($id);

            $newFilePath = $request->file['file_path'] ?? null;

            // check if file changed
            if ($newFilePath && $newFilePath != $document->file_path) {

                // determine parent id
                $parentId = $document->parent_id ?? $document->id;

                // get latest version
                $latestVersion = Document::where(function ($q) use ($parentId) {
                    $q->where('id', $parentId)
                        ->orWhere('parent_id', $parentId);
                })
                    ->orderByDesc('id')
                    ->first();

                // extract version number
                $versionNumber = intval(str_replace('v', '', $latestVersion->doc_version)) + 1;

                // create new version
                $newDocument                       = new Document();
                $newDocument->title                = $request->title;
                $newDocument->description          = $request->description;
                $newDocument->category             = $request->category;
                $newDocument->group                = $request->group;
                $newDocument->is_re_registration   = $request->is_re_registration;
                $newDocument->is_training_document = $request->is_training_document;
                $newDocument->download_alert       = $request->download_alert;
                $newDocument->drug_id              = $request->drug_id;
                $newDocument->sequence             = $request->sequence;

                $newDocument->file_name = $request->file['file_name'];
                $newDocument->file_path = $request->file['file_path'];
                $newDocument->file_type = $request->file['file_type'];
                $newDocument->file_size = $request->file['file_size'];
                $newDocument->mime      = $request->file['mime'];

                $newDocument->parent_id   = $parentId;
                $newDocument->doc_version = 'v' . $versionNumber;
                $newDocument->created_by  = $authUserId;

                $newDocument->save();

                CustomFunctions::audit(
                    module: 'Documents',
                    action: 'VERSION CREATE',
                    referenceId: $newDocument->id,
                    referenceTable: 'documents',
                    oldValues: $document->toArray(),
                    newValues: $newDocument->toArray(),
                    description: "New version {$newDocument->doc_version} created for document '{$newDocument->title}'. File updated from '{$document->file_name}' to '{$newDocument->file_name}'."
                );

                if ($request->is_re_registration == 1) {

                    $reason = $request->group . ' document updated.';

                    // PHARMACIST MEDICATION

                    $pharmacistMedications = PharmacistMedication::with('user', 'drug')
                        ->where('drug_id', $request->drug_id)
                        ->where('expired', 0)
                        ->get();
                    $emailTemplate = EmailTemplate::where('template_name', 'Expired due to Document Update')->first();

                    if (isset($emailTemplate)) {

                        foreach ($pharmacistMedications as $med) {

                            // Update
                            $med->update([
                                'expired'       => 1,
                                'expiry_reason' => $reason,
                                'updated_at'    => now(),
                                'updated_by'    => Auth::id(),
                            ]);

                            $user = $med->user;
                            $drug = $med->drug;

                            if ($user) {

                                if (
                                    $emailTemplate->is_mandatory === 1 ||
                                    ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                                ) {

                                    $userdata = [
                                        'firstname'      => $user->full_name,
                                        'drug_name'      => $drug->drug_name ?? '',
                                        'document_title' => $request->title ?? '',
                                        'expiry_reason'  => $reason,
                                    ];

                                    $parsedSubject    = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                                    $parsedContent    = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);

                                    Mail::to($user->email)->queue(
                                        new RegistrationRejectionMail(
                                            $parsedSubject,
                                            $parsedContent,
                                            $paresedSignature,
                                            null,
                                            null
                                        )
                                    );
                                }
                            }
                        }
                    }

                    // PRESCRIBER MEDICATION

                    $prescriberMedications = PrescriberMedication::with('user', 'drug')
                        ->where('drug_id', $request->drug_id)
                        ->where('expired', 0)
                        ->get();

                    if (isset($emailTemplate)) {
                        foreach ($prescriberMedications as $med) {

                            // Update
                            $med->update([
                                'expired'       => 1,
                                'expiry_reason' => $reason,
                                'updated_at'    => now(),
                                'updated_by'    => Auth::id(),
                            ]);

                            $user = $med->user;
                            $drug = $med->drug;

                            if ($user) {

                                if (
                                    $emailTemplate->is_mandatory === 1 ||
                                    ($emailTemplate->is_mandatory === 0 && $user->email_subscription == 1)
                                ) {
                                    $userdata = [
                                        'firstname'      => $user->full_name,
                                        'drug_name'      => $drug->drug_name ?? '',
                                        'document_title' => $request->title ?? '',
                                        'expiry_reason'  => $reason,
                                    ];

                                    $parsedSubject    = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                                    $parsedContent    = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                                    $paresedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);

                                    Mail::to($user->email)->queue(
                                        new RegistrationRejectionMail(
                                            $parsedSubject,
                                            $parsedContent,
                                            $paresedSignature,
                                            null,
                                            null
                                        )
                                    );
                                }
                            }
                        }
                    }
                    CustomFunctions::audit(
                        module: 'Documents',
                        action: 'RE-REGISTRATION IMPACT',
                        referenceId: $newDocument->id,
                        referenceTable: 'documents',
                        newValues: [
                            'drug_id' => $request->drug_id,
                            'document_title' => $newDocument->title,
                        ],
                        description: "Document '{$newDocument->title}' update triggered re-registration. Related pharmacist and prescriber medications were expired."
                    );

                }
                DB::commit();
                return response()->json([
                    'status'  => 'S',
                    'message' => 'New document version created',
                    'data'    => $newDocument,
                ]);
            }

            // file same → normal update
            $document->title                = $request->title;
            $document->description          = $request->description;
            $document->category             = $request->category;
            $document->drug_id              = $request->drug_id;
            $document->sequence             = $request->sequence;
            $document->is_re_registration   = $request->is_re_registration;
            $document->is_training_document = $request->is_training_document;
            $document->download_alert       = $request->download_alert;
            $document->group                = $request->group;

            if ($request->filled('file')) {
                $document->file_name = $request->file['file_name'];
                $document->file_path = $request->file['file_path'];
                $document->file_type = $request->file['file_type'];
                $document->file_size = $request->file['file_size'];
                $document->mime      = $request->file['mime'];
            }

            $document->updated_by = $authUserId;

            $document->save();
            
            $oldData = $document->getOriginal();

            $newData = $document->fresh()->toArray();

            CustomFunctions::audit(
                module: 'Documents',
                action: 'UPDATE',
                referenceId: $document->id,
                referenceTable: 'documents',
                oldValues: $oldData,
                newValues: $newData,
                description: "Document '{$document->title}' details updated"
            );
            DB::commit();
            return response()->json([
                'status'  => 'S',
                'message' => trans('returnmessage.updatedsuccessfully'),
                'data'    => $document,
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to edit Document details.
     *
     * @author: Santhosha G
     *
     * @created-on: 12 Feb, 2022
     *
     * @updated-on: N/A
     */
    public function editDocument($slug)
    {
        try {
            $documents = Document::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'documents' => $documents]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to Delete Document detail.
     *
     * @author: Santhosha G
     *
     * @created-on: 16 Feb, 2022
     *
     * @updated-on: N/A
     */
    public function delete($id)
    {
        try {
            $document = Document::find($id);

            if (! $document) {
                return response()->json([
                    'status'  => 'F',
                    'message' => 'Document not found',
                ]);
            }

            $path = $document->file_path;

            // delete physical file
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            // delete DB record
            $document->delete();

            return response()->json(['status' => 'S', 'message' => trans('returnmessage.deletedsuccessfully')]);

        } catch (\Exception $e) {
            Log::info($e);
            return response()->json([
                'status'  => 'F',
                'message' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to fetch documents based on anything inside the docunet crug data.
     *
     * @author: Santhosha G
     *
     * @created-on: 09 Mar 2026
     *
     * @updated-on: 09 Mar 2026
     */
    public function fetchDocuments($search = null)
    {
        try {

            $documents = Document::with(['drug'])
                ->when($search, function ($query) use ($search) {

                    $query->where(function ($q) use ($search) {

                        // Search in documents table
                        $q->where('title', 'LIKE', "%$search%")
                            ->orWhere('category', 'LIKE', "%$search%")
                            ->orWhere('description', 'LIKE', "%$search%");

                        // Search in drugs table
                        $q->orWhereHas('drug', function ($drug) use ($search) {
                            $drug->where('drug_name', 'LIKE', "%$search%");
                        });

                    });

                })
                ->get();

            return response()->json([
                'status'    => 'S',
                'documents' => $documents,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to fetch RAF documents.
     *
     * @author: Santhosha G
     *
     * @created-on: 09 Mar 2026
     *
     * @updated-on: 09 Mar 2026
     */
    public function fetchRafDocuments(Request $request)
    {
        try {
            Log::info('fetchRafDocuments');
            Log::info($request);
            $drugId = $request->drug_id;
            $patientCategory = $request->patient_category;

            $documents = PAFDocument::where('drug_id', $drugId)
                ->where('status', 1)
                ->where('category', 'Drugs')
                ->where('patient_category', $patientCategory)
                ->where('group', 'RAF')
                ->get()
                ->groupBy(function ($doc) {
                    return $doc->parent_id == 0 ? $doc->id : $doc->parent_id;
                })
                ->map(function ($group) {
                    return $group->sortByDesc(function ($item) {
                        return (int) str_replace('v', '', $item->doc_version);
                    })->first();
                })
                ->values();

            return response()->json([
                'status'    => 'S',
                'documents' => $documents,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'E',
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @function: to send documents download alert to admin.
     *
     * @author: Santhosha G
     *
     * @created-on: 21 Apr 2026
     *
     * @updated-on: N/A
     */
    public function downloadNotification(Request $request)
    {
        try {

            $user = auth()->user();

            if (! $user) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'User not authenticated',
                ]);
            }

            $document = Document::find($request->id);

            if (! $document) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Document not found',
                ]);
            }

            // GET TEMPLATE
            $emailTemplate = EmailTemplate::where('template_name', 'Pregnancy Form Download Alert')->first();

            if (! $emailTemplate) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'Email template not found',
                ]);
            }

            // FETCH PCG ADMINS (CORRECT WAY)
            $admins = User::with('role')
                ->whereHas('role', function ($q) {
                    $q->where('rolename', 'PCG Admin');
                })
                ->where('status', 1)
                ->get();

            if ($admins->isEmpty()) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'No admin users found',
                ]);
            }

            // COMMON DATA
            $userdata = [
                'document_name' => $document->title ?? 'Pregnancy Reporting Form',
                'user_name'     => $user->full_name,
                'user_role'     => $user->role->rolename ?? 'User',
                'user_email'    => $user->email,
                'download_time' => now()->format('d-m-Y h:i A'),
            ];

            $sentCount = 0;

            // SEND MAIL WITH CONDITION (LIKE YOUR OLD CODE)
            foreach ($admins as $admin) {

                if (
                    $emailTemplate->is_mandatory == 1 ||
                    ($emailTemplate->is_mandatory == 0 && $admin->email_subscription == 1)
                ) {

                    $parsedSubject   = CustomFunctions::EmailContentParser($emailTemplate->template_subject, $userdata);
                    $parsedContent   = CustomFunctions::EmailContentParser($emailTemplate->template_body, $userdata);
                    $parsedSignature = CustomFunctions::EmailContentParser($emailTemplate->template_signature, $userdata);

                    Mail::to($admin->email)->queue(
                        new RegistrationRejectionMail(
                            $parsedSubject,
                            $parsedContent,
                            $parsedSignature,
                            null,
                            null
                        )
                    );

                    $sentCount++;
                }
            }

            // NO MAIL SENT CASE
            if ($sentCount === 0) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'No admin users eligible to receive email',
                ]);
            }

            // AUDIT
            CustomFunctions::audit(
                module: 'Document Download',
                action: 'DOWNLOAD ALERT SENT',
                referenceId: $document->id,
                referenceTable: 'documents',
                oldValues: null,
                newValues: [
                    'document'          => $document->title,
                    'downloaded_by'     => $user->full_name,
                    'total_emails_sent' => $sentCount,
                ],
                description: 'Pregnancy form download alert sent to admin users'
            );

            return response()->json([
                'status'  => 'S',
                'message' => "$sentCount admin(s) notified successfully",
            ]);

        } catch (\Exception $e) {
            Log::error('Download Alert Error: ' . $e->getMessage());
            return response()->json([
                'status'  => 'E',
                'message' => 'Something went wrong',
            ]);
        }
    }
}
