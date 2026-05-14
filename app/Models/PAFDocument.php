<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PAFDocument extends Model
{
    use HasFactory, HasSlug;

    public $table = 'paf_documents';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'title',
        'parent_id',
        'description',
        'file_name',
        'file_path',
        'file_type',
        'file_size',
        'mime',
        'patient_category',
        'drug_id',
        'group',
        'sequence',
        'doc_version',
        'is_re_registration',
        'is_downloaded',
        'is_training_document',
        'download_alert',
        'status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }
    public function drug()
    {
        return $this->belongsTo(Drugs::class, 'drug_id');
    }
}
