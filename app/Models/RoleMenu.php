<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoleMenu extends Model
{
    use HasFactory;
    public $table = 'role_menu';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'role_id',
        'menu_id',
        'created_by',
        'updated_by',
    ];
}
