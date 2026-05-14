<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class ActionMaster extends Model
{
    use HasFactory, HasSlug;

    public $table = 'action_master';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'action_name'
        , 'category'
        , 'description'
        , 'status'
        , 'slug'
        , 'created_by'
        , 'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['category', 'action_name'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
}
