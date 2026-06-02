<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplierIndividual extends Model
{
    protected $table = 'supplier_individual';

    protected $fillable = [
        'supplier_id',
        'name',
        'telephone',
        'email',
        'position',
        'active',
        'created_by',
        'updated_by',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id', 'id');
    }
}
