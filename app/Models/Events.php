<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Events extends Model implements Searchable
{
    use HasSlug;
    use HasFactory;
    public $table = 'events';
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $appends = ['review_by', 'is_expired'];
    protected $fillable = [
        'title'
        , 'header_id'
        , 'lang'
        , 'description'
        , 'start_date'
        , 'end_date'
        , 'image_path'
        , 'status'
        , 'slug'
        , 'stor_type'
        , 'email'
        , 'mobile_code'
        , 'mobile'
        , 'meta_title'
        , 'store_id'
        , 'floor'
        , 'meta_description'
        , 'created_by'
        , 'updated_by',
    ];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug');
    }

    public function getReviewByAttribute()
    {
        if ($this->approved_by) {
            $user = User::where('id', $this->approved_by)->first();
            return $user->salutation . ' ' . $user->name . ' ' . $user->lastname;
        } else {
            return null;
        }
    }

    public function getIsExpiredAttribute()
    {
        $today = now()->toDateString();
        if ($this->end_date < $today) {
            return 1;
        } else {
            return 0;
        }
    }

    public function storeName()
    {
        return $this->hasOne('App\Models\Stores', 'header_id', 'store_id');
    }
    public function floorDetails()
    {
        return $this->hasOne('App\Models\LookUp', 'shortname', 'floor');
    }
    public function getSearchResult(): SearchResult
    {
        $type = "events";

        return new \Spatie\Searchable\SearchResult(
            $this,
            $type
        );
    }

}
