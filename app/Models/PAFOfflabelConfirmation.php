<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PAFOfflabelConfirmation extends Model
{
    use HasFactory;

    public $table = 'paf_offlabel_confirmation';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'paf_details_id',
        'type',
        'confirmation',
        'version',
        'created_by',
        'updated_by',
    ];

}
