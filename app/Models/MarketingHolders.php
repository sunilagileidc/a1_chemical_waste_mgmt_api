<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class MarketingHolders extends Model
{
    use HasFactory, HasSlug;

    public $table = 'marketing_holders';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'contact_name',
        'contact_email',
        'logo',
        'description',
        'status',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('contact_name')
            ->saveSlugsTo('slug');
    }
    public function drugs()
    {
        return $this->belongsToMany(
            Drugs::class,
            'drug_marketing_holders',
            'marketing_holder_id',
            'drug_id'
        );
    }
}
