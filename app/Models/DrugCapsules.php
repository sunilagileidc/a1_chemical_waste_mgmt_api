<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugCapsules extends Model
{
    use HasFactory;

    public $table = 'drug_capsules';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'drug_id',
        'lookup_id',
        'no_of_capsules',
        'created_by',
        'updated_by',
    ];
}
