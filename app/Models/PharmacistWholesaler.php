<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PharmacistWholesaler extends Model
{
    use HasFactory;

    public $table = 'pharmacist_wholesaler';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'pharmacist_id'
        , 'wholesaler_id'
        , 'name'
        , 'acc_no'
        , 'created_by'
        , 'updated_by',
    ];
    public function wholesaler()
    {
        return $this->Hasone(Wholesalers::class, 'id', 'wholesaler_id');
    }
}
