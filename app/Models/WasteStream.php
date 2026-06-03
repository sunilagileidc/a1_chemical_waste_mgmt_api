<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WasteStream extends Model
{
    use HasFactory;

    protected $table = 'waste_streams';

    protected $fillable = [
        'waste_code',
        'waste_description',
        'is_hazard',
        'waste_components',
        'waste_ewc',
        'waste_color',
        'waste_physical_form',
        'waste_haz_code',
        'waste_risk_pharse',
        'waste_un_no',
        'waste_pkg_grp',
        'waste_un_cls',
        'waste_ship_name',
        'waste_ass_raj',
        'waste_rd_color',
        'slug',
        'active',
        'created_by',
        'updated_by',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($waste) {

            if (! $waste->slug) {
                $waste->slug = Str::slug($waste->waste_code)
                . '-' . time();
            }

        });
    }
}
