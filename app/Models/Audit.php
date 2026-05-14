<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Audit extends Model
{
    use HasFactory;

    public $table = 'audits';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id',
        'module',
        'action',
        'reference_id',
        'reference_table',
        'old_values',
        'new_values',
        'changed_fields',
        'ip_address',
        'user_agent',
        'url',
        'status',
        'description',
    ];

    protected $casts = [
        'old_values'     => 'array',
        'new_values'     => 'array',
        'changed_fields' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
