<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PafHeader extends Model
{
    use HasFactory, HasSlug;

    public $table = 'paf_header';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'patient_no',
        'gender',
        'is_active',
        'paf_status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['patient_no', 'drug_id'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function pafDetails()
    {
        return $this->hasMany(PafDetails::class, 'paf_header_id')->orderBy('paf_no','desc');
    }

}
