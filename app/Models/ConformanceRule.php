<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ConformanceRule extends Model
{
    use HasFactory, HasSlug;

    public $table = 'conformance_rules';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'conformance_type'
        , 'description'
        , 'status'
        , 'slug'
        , 'created_by'
        , 'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('conformance_type')
            ->saveSlugsTo('slug');
    }
}
