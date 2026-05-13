<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Cities extends Model
{
    use HasFactory, HasSlug;

    public $table = 'cities';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name'
        , 'header_id'
        , 'lang'
        , 'state_id'
        , 'status'
        , 'slug'
        , 'country_id'
        , 'created_by'
        , 'updated_by',
    ];

    public function state()
    {
        return $this->hasOne('App\Models\States', 'id', 'state_id');
    }
    public function country()
    {
        return $this->hasOne('App\Models\Countries', 'id', 'country_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
