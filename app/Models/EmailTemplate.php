<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class EmailTemplate extends Model
{
    use HasFactory, HasSlug;

    public $table = 'email_templates';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'template_name',
        'template_subject',
        'template_body',
        'template_signature',
        'can_override',
        'template_type_id',
        'status',
        'lang',
        'slug',
        'is_mandatory',
        'createdby',
        'lastupdatedby',
        'created_at',
        'updated_at',
        'org_id',
        'is_standard',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('template_name')
            ->saveSlugsTo('slug');
    }
}
