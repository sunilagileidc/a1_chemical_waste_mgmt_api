<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    public $table = 'roles';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    //protected $appends = ['parent_name'];

    protected $fillable = [
        'id',
        'rolename',
        'role_display_name',
        'roledescription',
        'slug',
        'status',
        'created_by',
        'updated_by',
    ];

}
