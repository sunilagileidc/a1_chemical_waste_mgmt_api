<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PolicyAssignedQuestions extends Model
{
    use HasFactory, HasSlug;

    public $table = 'policy_assigned_questions';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'parent_id',
        'q_type',
        'sequence',
        'question',
        'description',
        'attach_doc',
        'doc_title',
        'doc_link',
        'status',
        'slug',
        'created_by',
        'updated_by'
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['q_type', 'parent_id', 'sequence'])
            ->saveSlugsTo('slug');
    }
}
