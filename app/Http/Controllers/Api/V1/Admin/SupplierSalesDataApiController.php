<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Imports\SupplierSalesDataImport;
use App\Models\Drugs;
use App\Models\Institutions;
use App\Models\PafDetails;
use App\Models\SupplierSalesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class SupplierSalesDataApiController extends Controller
{
    /**
     * @function: to fetch supplier sales details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 May 2026
     *
     * @updated-on: N/A
     */
    public function index()
    {
        try {
            $supplier_sales_data = SupplierSalesData::latest()->get();

            foreach ($supplier_sales_data as $row) {

                $text = trim($row->product_description);

                // 1. EXTRACT DRUG NAME
                $drugName = null;

                $cleanText = preg_replace('/^\d+\s?(mg|ml|g|mcg)\s*-\s*/i', '', $text);

                preg_match('/^(.*?)\s+(HARD|CAPS|TABLET)/i', $cleanText, $match);

                if (! empty($match[1])) {
                    $drugName = trim($match[1]);
                } else {
                    $drugName = explode(' ', $cleanText)[0] ?? null;
                }

                // 2. EXTRACT STRENGTH
                preg_match_all('/\b\d+\s?(MG|ML|G|MCG)\b/i', $text, $matches);

                $strength = ! empty($matches[0]) ? end($matches[0]) : null;

                $normalizedStrength = $strength
                    ? strtolower(str_replace(' ', '', $strength))
                    : null;

                // 3. EXTRACT CORE DRUG NAME
                $coreDrugName = null;

                if ($drugName) {
                    // remove words like Tablet, Capsule etc.
                    $coreDrugName = preg_replace('/\b(tablet|capsule|caps|hard)\b/i', '', $drugName);
                    $coreDrugName = trim($coreDrugName);
                }

                // 4. GET DRUG ID
                $drugId = null;

                if ($coreDrugName) {
                    $drug = Drugs::whereRaw('LOWER(drug_name) LIKE ?', ['%' . strtolower($coreDrugName) . '%'])
                        ->first();

                    $drugId = $drug->id ?? null;
                }

                // 5. GET INSTITUTION IDS
                $institution_ids = [];

                $pharmacy_name = trim($row->customer_name);

                if (! empty($pharmacy_name)) {
                    $institution_ids = Institutions::whereHas('pharmacy', function ($q) use ($pharmacy_name) {
                        $q->where('name', 'LIKE', '%' . $pharmacy_name . '%');
                    })
                        ->pluck('id')
                        ->toArray();
                }

                // 6. GET PAF IDs
                $pafIds    = [];
                $totalCaps = 0;

                if ($drugId) {

                    $query = PafDetails::latestVersion()
                        ->where('drug_id', $drugId)
                        ->whereRaw('LOWER(status) = ?', ['dispensed']);

                    // Apply institution filter only if exists
                    if (! empty($institution_ids)) {
                        $query->whereIn('institution_id', $institution_ids);
                    }

                    $pafIds = $query->pluck('id')->toArray();

                    // 7. CALCULATE CAPS
                    if (! empty($pafIds) && $normalizedStrength) {

                        $totalCaps = DB::table('paf_drug_cycles')
                            ->whereIn('paf_details_id', $pafIds)
                            ->whereRaw('LOWER(REPLACE(drug_strength, " ", "")) = ?', [$normalizedStrength])
                            ->sum('cap_per_cycle');
                    }
                }

                // 8. ASSIGN VALUES
                $row->drug_name_extracted     = $drugName;
                $row->drug_strength_extracted = $strength;
                $row->drug_id                 = $drugId;
                $row->paf_details_ids         = $pafIds;
                $row->dispensed_caps          = $totalCaps;
            }

            return response()->json([
                'status'              => 'S',
                'message'             => trans('returnmessage.dataretreived'),
                'supplier_sales_data' => $supplier_sales_data,
            ]);

        } catch (\Exception $e) {

            Log::error('Supplier Sales Error: ' . $e->getMessage());

            return response()->json([
                'status'     => 'E',
                'message'    => trans('returnmessage.error_processing'),
                'error_data' => $e->getMessage(),
            ]);
        }
    }
    /**
     * @function: to bulk upload for supplier sales details.
     *
     * @author: Santhosha G
     *
     * @created-on: 05 May 2026
     *
     * @updated-on: N/A
     */

    public function upload(Request $request)
    {
        DB::beginTransaction();

        try {
            // Validation (Excel + CSV)
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:csv,xlsx,xls|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status'  => 'E',
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            // Import file (handles Excel + CSV automatically)
            Excel::import(new SupplierSalesDataImport, $request->file('file'));

            DB::commit();

            return response()->json([
                'status'  => 'S',
                'message' => 'File uploaded and processed successfully.',
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status'  => 'E',
                'message' => 'Upload failed. Please check file format.',
                'error'   => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }
}
