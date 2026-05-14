<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugCycles extends Model
{
    use HasFactory;

    public $table = 'drug_cycles';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'drug_id',
        'lookup_id',
        'no_of_cycle_weeks',
        'created_by',
        'updated_by',
    ];
}
