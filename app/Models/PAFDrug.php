<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PAFDrug extends Model
{
    use HasFactory, HasSlug;

    public $table = 'paf_drug';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'drug_name',
        'capsule_strength',
        'capsules_per_cyle',
        'number_of_cycles',
        'total_capsules',
        'status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('drug_name')
            ->saveSlugsTo('slug');
    }
}
