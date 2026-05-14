<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PAFNonConformance extends Model
{
    use HasFactory;

    public $table = 'paf_nonconformance';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'paf_details_id',
        'note',
        'type',
    ];
}
