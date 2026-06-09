<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\QuotationItem;
use App\Models\SalesQuotation;
use Exception;
use Illuminate\Http\Request;
use Validator;

class SalesQuotationApiController extends Controller
{

    public function index()
    {

        try {

            $quotations = SalesQuotation::with([
                'customer',
                'items.wasteStream'])->orderBy('id', 'desc')->get();

            return response()->json([
                'status'     => 'S',
                'message'    => 'Details retrieved successfully',
                'quotations' => $quotations,
            ]);

        } catch (Exception $e) {

            return response()->json([
                'status'     => 'E',
                'message'    => 'Error processing details',
                'error_data' => $e->getMessage(),
            ]);

        }

    }

    public function save(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'customer_id' => 'required',
                'items'       => 'required|array',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        try {

            // ✅ CHECK IF UPDATE OR CREATE
            if (! empty($request->id)) {

                $quotation = SalesQuotation::find($request->id);

                if (! $quotation) {
                    return response()->json([
                        'status'  => 'E',
                        'message' => 'Quotation not found',
                    ]);
                }

                // ✅ update ALL fields dynamically (safe version)
                $quotation->update($request->only([
                    'customer_id',
                    'job_name',
                    'quotation_date',
                    'transport_cost',
                    'document_cost',
                    'status',
                ]));

                // 🔥 replace items completely
                QuotationItem::where('sales_quotation_id', $quotation->id)->delete();
            } else {

                $quotation = SalesQuotation::create([
                    'quotation_number' => 'QT' . time(),
                    'customer_id'      => $request->customer_id,
                    'job_name'         => $request->job_name,
                    'quotation_date'   => $request->quotation_date,
                    'transport_cost'   => $request->transport_cost ?? 0,
                    'document_cost'    => $request->document_cost ?? 0,
                    'sub_total'        => 0,
                    'total_cost'       => 0,
                    'status'           => 'draft',
                    'slug'             => str()->slug('qt-' . time()),
                    'active'           => 1,
                    'created_by'       => auth()->id(),
                    'updated_by'       => auth()->id(),
                ]);
            }

            $total = 0;

            foreach ($request->items as $key => $item) {

                $itemTotal = $item['quote_qty'] * $item['quote_unit_price'];

                QuotationItem::create([
                    'sales_quotation_id' => $quotation->id,
                    'waste_stream_id'    => $item['waste_stream_id'],
                    'item_order'         => $key,
                    'quote_size'         => $item['quote_size'] ?? null,
                    'quote_qty'          => $item['quote_qty'] ?? 0,
                    'quote_unit_price'   => $item['quote_unit_price'] ?? 0,
                    'quote_vat_exclude'  => $item['quote_vat_exclude'] ?? 0,
                    'vat'                => $item['vat'] ?? 0,
                    'quote_total_price'  => $itemTotal,
                    'active'             => 1,
                    'created_by'         => auth()->id(),
                    'updated_by'         => auth()->id(),
                ]);

                $total += $itemTotal + ($item['vat'] ?? 0);
            }

            $quotation->update([
                'sub_total'  => $total,
                'total_cost' => $total + ($request->transport_cost ?? 0) + ($request->document_cost ?? 0),
            ]);

            return response()->json([
                'status'       => 'S',
                'message'      => 'Quotation saved successfully',
                'quotation_id' => $quotation->id,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'status'     => 'E',
                'message'    => 'Error processing details',
                'error_data' => $e->getMessage(),
            ]);
        }
    }

    public function bySlug($slug)
    {

        $quotation =
        SalesQuotation::with([
            'customer',
            'items.wasteStream',
        ])
            ->where('slug', $slug)
            ->first();

        return response()->json([
            'status'    => 'S',
            'quotation' => $quotation,
        ]);

    }

}
