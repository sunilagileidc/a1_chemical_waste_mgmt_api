<?php

namespace App\Models;

use App\Models\LookUp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    public $table = 'email_templates';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'template_name',
        'template_subject',
        'template_body',
        'template_signature',
        'can_override',
        'template_type_id',
        'status',
        'lang',
        'slug',
        'createdby',
        'lastupdatedby',
        'created_at',
        'updated_at',
        'org_id',
        'is_standard',
    ];

}
