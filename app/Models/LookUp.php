<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class LookUp extends Model
{
    use HasFactory, HasSlug;

    public $table = 'lookups';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'moduleid'
        , 'header_id'
        , 'lang'
        , 'shortname'
        , 'longname'
        , 'status'
        , 'slug'
        , 'seq'
        , 'parent_id'
        , 'description'
        , 'icon'
        , 'created_at'
        , 'updated_at',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('shortname')
            ->saveSlugsTo('slug');
    }

    public function childlookup()
    {
        return $this->hasMany('App\Models\LookUp', 'parent_id', 'id');
    }
    public function parentlookup()
    {
        return $this->hasOne('App\Models\LookUp', 'id', 'parent_id');
    }

}
