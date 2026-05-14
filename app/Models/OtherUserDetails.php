<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class OtherUserDetails extends Model
{
    use HasFactory, HasSlug;

    public $table = 'other_user_details';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id'
        , 'reg_no'
        , 'job_title'
        , 'institution_id'
        , 'slug'
        , 'created_by'
        , 'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['reg_no', 'job_title'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
    public function institution()
    {
        return $this->belongsTo(\App\Models\Institutions::class, 'institution_id', 'id');
    }
}
