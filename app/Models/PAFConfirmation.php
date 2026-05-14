<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PAFConfirmation extends Model
{
    use HasFactory;

    public $table = 'paf_confirmation';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'paf_detail_id',
        'is_confirmed',
        'role',
        'created_by',
        'updated_by',
    ];
}
