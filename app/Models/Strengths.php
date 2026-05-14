<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Strengths extends Model
{
    use HasFactory;
    public $table = 'drug_strength';

    protected $dates = [
        'created_at',
        'updated_at',
    ];
    protected $fillable = [
        'drug_id',
        'capsule_strength',
        'status',
        'created_by',
        'updated_by',
    ];
    // ✅ CORRECT RELATIONSHIP
    public function drug()
    {
        return $this->belongsTo(Drugs::class, 'drug_id', 'id');
    }
}
