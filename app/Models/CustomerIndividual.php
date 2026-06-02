<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerIndividual extends Model
{
    protected $table = 'customer_individual';

    protected $fillable = [
        'customer_id',
        'name',
        'telephone',
        'email',
        'position',
        'active',
        'created_by',
        'updated_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id', 'id');
    }
}
