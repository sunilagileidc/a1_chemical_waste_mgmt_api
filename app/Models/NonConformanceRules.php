<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Str;

class NonConformanceRules extends Model
{
    use HasFactory, HasSlug;

    public $table = 'nonconformance_rules';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'conformance_type',
        'description',
        'status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(function ($model) {
                return $model->conformance_type . '-' . Str::lower(Str::random(4));
            })
            ->saveSlugsTo('slug');
    }
}
