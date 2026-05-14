<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginAudit extends Model
{
    use HasFactory;

    public $table = 'login_audit';

    protected $dates = [
        'created_at',
        'updated_at',
        'login_at',
    ];

    protected $fillable = [
        'user_id'
        , 'ip_address'
        , 'country_code'
        , 'country_name'
        , 'user_agent'
        , 'status'
        , 'created_by'
        , 'updated_by',
    ];
}
