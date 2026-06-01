<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;

    protected $table = 'supplier';

    protected $fillable = [
        'supplier_name',
        'supplier_address',
        'supplier_postcode',
        'supplier_telephone',
        'supplier_email',
        'supplier_license',
        'slug',
        'active',
        'created_by',
        'updated_by',
    ];
}
