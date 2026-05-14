<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PafRequestInformation extends Model
{
    use HasFactory;

    public $table = 'paf_request_information';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'requested_users' => 'array',
    ];

    protected $fillable = [
        'paf_detail_id',
        'paf_no',
        'patient_id',
        'request_note',
        'requested_users',
        'reminder_count',
        'is_closed',
        'last_reminder_sent_at',
        'created_by',
        'updated_by',
    ];
}
