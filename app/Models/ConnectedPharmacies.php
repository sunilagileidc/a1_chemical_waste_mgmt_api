<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConnectedPharmacies extends Model
{
    use HasFactory;

    public $table = 'connected_pharmacies';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'connected_pharmacy_id'
        , 'user_id'
        , 'institution_id'
        , 'status'
        , 'created_by'
        , 'updated_by',
    ];
    public function pharmacy()
    {
        return $this->hasMany(Institutions::class, 'id', 'connected_pharmacy_id');
    }
    public function pharmacy_data()
    {
        return $this->hasOne(Institutions::class, 'id', 'connected_pharmacy_id');
    }

    // public function getSlugOptions(): SlugOptions
    // {
    //     return SlugOptions::create()
    //         ->generateSlugsFrom('name')
    //         ->saveSlugsTo('slug');
    // }
}
