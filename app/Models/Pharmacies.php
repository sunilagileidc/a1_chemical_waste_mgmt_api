<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Pharmacies extends Model
{
    use HasFactory, HasSlug;

    public $table = 'pharmacies';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name'
        , 'type'
        , 'address'
        , 'postcode'
        , 'status'
        , 'slug'
        , 'contact_name'
        , 'contact_email'
        , 'created_by'
        , 'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

}
