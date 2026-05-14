<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugMarketingHolders extends Model
{
    use HasFactory;

    public $table = 'drug_marketing_holders';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'drug_id',
        'marketing_holder_id',
        'created_by',
        'updated_by',
    ];
}
