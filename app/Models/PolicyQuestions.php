<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PolicyQuestions extends Model
{
    use HasFactory, HasSlug;

    public $table = 'policy_questions';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'title',
        'description',
        'linked_to',
        'ref_type',
        'ref_value',
        'sequence',
        'status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['title', 'ref_type', 'ref_value'])
            ->saveSlugsTo('slug');
    }
}
