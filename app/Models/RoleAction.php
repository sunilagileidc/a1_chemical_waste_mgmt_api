<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleAction extends Model
{
    use HasFactory;
    public $table = 'role_action';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'role_id',
        'action_id',
        'status',
        'created_by',
        'updated_by',
    ];
}
