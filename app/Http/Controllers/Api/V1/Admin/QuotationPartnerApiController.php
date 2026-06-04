<?php
namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Haulier;
use App\Models\QuotationPartner;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Validator;

class QuotationPartnerApiController extends Controller
{
    public function index($quotation_id)
    {

        $data = QuotationPartner::where(
            'sales_quotation_id',
            $quotation_id
        )
            ->get();

        foreach ($data as $row) {

            if ($row->partner_type == 'supplier') {
                $row->partner = Supplier::find($row->partner_id);
            }

            if ($row->partner_type == 'haulier') {
                $row->partner = Haulier::find($row->partner_id);
            }

        }

        return response()->json([
            'status'   => 'S',
            'partners' => $data,
        ]);

    }
    public function save(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'sales_quotation_id' => 'required|exists:sales_quotations,id',
                'partner_type'       => 'required',
                'partner_id'         => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json([
                'status'  => 'E',
                'message' => $validator->errors()->all(),
            ]);
        }

        QuotationPartner::updateOrCreate(

            [
                'id' => $request->id,
            ],

            [
                'sales_quotation_id' => $request->sales_quotation_id,
                'partner_type'       => $request->partner_type,
                'partner_id'         => $request->partner_id,
                'quotation_date'     => $request->quotation_date,
                'transport_cost'     => $request->transport_cost ?? 0,
                'document_cost'      => $request->document_cost ?? 0,
                'fuel_charge'        => $request->fuel_charge ?? 0,
                'demurrage_charge'   => $request->demurrage_charge ?? 0,
                'load_type'          => $request->load_type,
                'load_other'         => $request->load_other,
                'number_pallets'     => $request->number_pallets,
                'haulier_notes'      => $request->haulier_notes,
                'status'             => 'pending',
                'active'             => 1,
                'created_by'         => auth()->id(),
                'updated_by'         => auth()->id(),
                'created_at'         => now(),
                'updated_at'         => now(),
            ]

        );

        return response()->json([

            'status'       => 'S',
            'message'      => 'Saved successfully',
            'quotation_id' => $request->sales_quotation_id,

        ]);

    }
}
