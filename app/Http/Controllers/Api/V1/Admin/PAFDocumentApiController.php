<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\CustomClass\CustomFunctions;
use App\Http\Controllers\Controller;
use App\Mail\RegistrationRejectionMail;
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

class PAFDocumentApiController extends Controller
{
    /**
     * @function: to fetch PAF document data.
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
            $documents = PAFDocument::whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('paf_documents')
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
            $document = PAFDocument::where('slug', $slug)->firstOrFail();

            // determine parent id
            $parentId = $document->parent_id ?? $document->id;

            // fetch all versions except the current one
            $documents = PAFDocument::where(function ($query) use ($parentId) {
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
     * @function: to Upload PAF document data.
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
                'patient_category'             => 'required|string|max:100',
                'group'                => 'nullable|string',
                'is_re_registration'   => 'integer',
                'is_training_document' => 'integer',
                'download_alert'       => 'integer',
                'description'          => 'nullable|string',
                'sequence'             => 'nullable|integer',
                'file'                 => 'required|array',
                'file.file_name'       => 'required|string',
                'file.file_path'       => 'required|string',
                'file.file_type'       => 'required|string',
                'file.file_size'       => 'required|integer',
                'file.mime'            => 'required|string',
            ]);
            DB::beginTransaction();
            $authUserId = Auth::id();

            $document = PAFDocument::create([
                'title'                => $request->title,
                'description'          => $request->description,
                'patient_category'     => $request->patient_category,
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
                module: 'PAF Documents',
                action: 'CREATE',
                referenceId: $document->id,
                referenceTable: 'paf_documents',
                newValues: $document->toArray(),
                description: "New document '{$document->title}' created under patient patient category '{$document->patient_category}'" . ($document->group ? " (Group: {$document->group})" : "") . ". File: {$document->file_name} ({$document->file_type})"
            );
            DB::commit();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.createdsuccessfully'), 'data' => $document]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to updating the PAF document data.
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
                'patient_category'             => 'required|string|max:100',
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

            $document = PAFDocument::findOrFail($id);

            $newFilePath = $request->file['file_path'] ?? null;

            // check if file changed
            if ($newFilePath && $newFilePath != $document->file_path) {

                // determine parent id
                $parentId = $document->parent_id ?? $document->id;

                // get latest version
                $latestVersion = PAFDocument::where(function ($q) use ($parentId) {
                    $q->where('id', $parentId)
                        ->orWhere('parent_id', $parentId);
                })
                    ->orderByDesc('id')
                    ->first();

                // extract version number
                $versionNumber = intval(str_replace('v', '', $latestVersion->doc_version)) + 1;

                // create new version
                $newDocument                       = new PAFDocument();
                $newDocument->title                = $request->title;
                $newDocument->description          = $request->description;
                $newDocument->patient_category     = $request->patient_category;
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
                    module: 'PAF Documents',
                    action: 'VERSION CREATE',
                    referenceId: $newDocument->id,
                    referenceTable: 'paf_document',
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
                    $emailTemplate = EmailTemplate::where('template_name', 'Expired due to PAFDocument Update')->first();

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
                        module: 'PAF Documents',
                        action: 'RE-REGISTRATION IMPACT',
                        referenceId: $newDocument->id,
                        referenceTable: 'paf_documents',
                        newValues: [
                            'drug_id' => $request->drug_id,
                            'document_title' => $newDocument->title,
                        ],
                        description: "PAFDocument '{$newDocument->title}' update triggered re-registration. Related pharmacist and prescriber medications were expired."
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
            $document->patient_category     = $request->patient_category;
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
                module: 'PAF Documents',
                action: 'UPDATE',
                referenceId: $document->id,
                referenceTable: 'paf_documents',
                oldValues: $oldData,
                newValues: $newData,
                description: "PAFDocument '{$document->title}' details updated"
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
     * @function: to edit PAFDocument details.
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
            $documents = PAFDocument::where('slug', $slug)->first();
            return response()->json(['status' => 'S', 'message' => trans('returnmessage.dataretreived'), 'documents' => $documents]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'E', 'message' => trans('returnmessage.error_processing'), 'error_data' => $e->getmessage()]);
        }
    }

    /**
     * @function: to Delete PAFDocument detail.
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
            $document = PAFDocument::find($id);

            if (! $document) {
                return response()->json([
                    'status'  => 'F',
                    'message' => 'PAFDocument not found',
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
     * @function: to fetch PAF document based on anything inside the docunet crug data.
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

            $documents = PAFDocument::with(['drug'])
                ->when($search, function ($query) use ($search) {

                    $query->where(function ($q) use ($search) {

                        // Search in PAF document table
                        $q->where('title', 'LIKE', "%$search%")
                            ->orWhere('patient_category', 'LIKE', "%$search%")
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
     * @function: to send PAF document download alert to admin.
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

            $document = PAFDocument::find($request->id);

            if (! $document) {
                return response()->json([
                    'status'  => 'E',
                    'message' => 'PAFDocument not found',
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
                module: 'PAFDocument Download',
                action: 'DOWNLOAD ALERT SENT',
                referenceId: $document->id,
                referenceTable: 'paf_documents',
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
