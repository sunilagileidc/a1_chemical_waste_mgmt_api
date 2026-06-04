<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesQuotation extends Model
{
    protected $fillable = [

        'quotation_number',
        'job_name',
        'customer_id',
        'user_id',
        'quotation_date',
        'transport_cost',
        'document_cost',
        'sub_total',
        'total_cost',
        'quote_saved',
        'quote_finalised',
        'status',
        'slug',
        'active',
        'created_by',
        'updated_by',
    ];

    public function items()
    {
        return $this->hasMany(
            QuotationItem::class,
            'sales_quotation_id'
        );
    }

    public function partners()
    {
        return $this->hasMany(
            QuotationPartner::class,
            'quotation_id'
        );
    }

    public function customer()
    {
        return $this->belongsTo(
            Customer::class,
            'customer_id'
        );
    }

}
