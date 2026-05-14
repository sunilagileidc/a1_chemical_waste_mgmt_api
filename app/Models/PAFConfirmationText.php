<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PAFConfirmationText extends Model
{
    use HasFactory;

    public $table = 'paf_confirmation_text';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'type',
        'drug_id',
        'patient_category',
        'note',
        'status',
        'created_by',
        'updated_by',
    ];
}
