<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationPartner extends Model
{

    protected $fillable = [

        'sales_quotation_id',
        'partner_type',
        'partner_id',
        'quotation_date',
        'transport_cost',
        'document_cost',
        'sub_total',
        'total_cost',
        'manual_figures',
        'fuel_charge',
        'demurrage_charge',
        'load_type',
        'load_other',
        'number_pallets',
        'haulier_notes',
        'quote_finalised',
        'status',
        'active',
        'created_by',
        'updated_by',

    ];

    public function quotation()
    {
        return $this->belongsTo(
            SalesQuotation::class,
            'sales_quotation_id'
        );
    }

    public function partner()
    {
        return $this->belongsTo(
            Supplier::class,
            'partner_id'
        );
    }
    public function supplier()
    {
        return $this->belongsTo(
            Supplier::class,
            'partner_id'
        );
    }

    public function haulier()
    {
        return $this->belongsTo(
            Haulier::class,
            'partner_id'
        );
    }

}
