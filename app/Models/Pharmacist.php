<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pharmacist extends Model
{
    use HasFactory;
    public $table = 'pharmacist_details';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'reg_no',
        'phone_no',
        'dispensing_address',
        'delivery_address',
        'ordering_address',
        'institution_type',
        'institution_name',
        'slug',
        'created_by',
        'updated_by',
    ];

}
