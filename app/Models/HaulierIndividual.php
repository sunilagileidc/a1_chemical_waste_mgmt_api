<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HaulierIndividual extends Model
{
    protected $table = 'haulier_individual';

    protected $fillable = [
        'haulier_id',
        'name',
        'telephone',
        'email',
        'position',
        'active',
        'created_by',
        'updated_by',
    ];

    public function haulier()
    {
        return $this->belongsTo(Haulier::class, 'haulier_id', 'id');
    }
}
