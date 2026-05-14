<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DrugIndications extends Model
{
    use HasFactory;

    public $table = 'drug_indications';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $appends = ['indication_name'];

    protected $fillable = [
        'drug_id',
        'indication_id',
        'created_by',
        'updated_by',
    ];

    public function getIndicationNameAttribute()
    {
        if ($this->indication_id) {
            $name = Indications::where('id', $this->indication_id)->value('name');
            return $name;
        } else {
            return null;
        }
    }


}
