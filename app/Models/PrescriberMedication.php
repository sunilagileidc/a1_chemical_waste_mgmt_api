<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrescriberMedication extends Model
{
    use HasFactory;

    public $table = 'prescriber_medication';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'prescriber_id'
        , 'user_id'
        , 'drug_id'
        , 'is_check'
        , 'start_date'
        , 'end_date'
        , 'version'
        , 'expired'
        , 'expiry_reason'
        , 'created_by'
        , 'updated_by',
    ];
    public function drug()
    {
        return $this->belongsTo(Drugs::class, 'drug_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}