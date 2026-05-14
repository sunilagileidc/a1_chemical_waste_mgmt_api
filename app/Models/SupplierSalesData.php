<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SupplierSalesData extends Model
{
    use HasFactory, HasSlug;

    public $table = 'supplier_sales_data';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'supplier',
        'invoice_date',
        'invoice_no',
        'order_ref',
        'pip_code',
        'account_no',
        'customer_name',
        'address1',
        'address2',
        'address3',
        'postcode',
        'quantity',
        'pack',
        'product_description',
        'batch_no',
        'status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('customer_name')
            ->saveSlugsTo('slug');
    }
}
