<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuotationItem extends Model
{

    protected $table = "quotation_items";
    protected $casts = [
        'quote_vat_exclude' => 'integer',
        'vat'               => 'decimal:2',
    ];

    protected $fillable = [

        'sales_quotation_id',
        'waste_stream_id',
        'item_order',
        'quote_size',
        'quote_qty',
        'quote_parameters',
        'quote_unit_price',
        'quote_vat_exclude',
        'quote_total_price',
        'supplier_id',
        'vat',
        'active',
        'created_by',
        'updated_by',

    ];

    public function wasteStream()
    {
        return $this->belongsTo(
            WasteStream::class,
            'waste_stream_id'
        );
    }

}
