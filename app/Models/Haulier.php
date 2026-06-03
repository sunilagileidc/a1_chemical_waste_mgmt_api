<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Haulier extends Model
{
    use HasFactory;

    protected $table = 'haulier';

    protected $fillable = [
        'haulier_name',
        'haulier_address',
        'haulier_postcode',
        'haulier_telephone',
        'haulier_email',
        'haulier_license',
        'slug',
        'active',
        'created_by',
        'updated_by',
    ];
    public function individuals()
    {
        return $this->hasMany(HaulierIndividual::class, 'haulier_id', 'id');
    }
}
