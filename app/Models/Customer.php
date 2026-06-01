<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $table = 'customer';

    protected $fillable = [
        'company_name',
        'company_address',
        'company_postcode',
        'company_telephone',
        'company_email',
        'active',
        'hwr_code',
        'hwr_expiry_date',
        'sic_code',
        'sic_desc',
        'slug',
        'created_by',
        'updated_by',
    ];
}