<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class SystemParameters extends Model
{
    use HasFactory, HasSlug;

    public $table = 'system_parameter';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'parameter_name'
        , 'parameter_value'
        , 'description'
        , 'is_file_upload'
        , 'slug'
        , 'status'
        , 'created_by'
        , 'updated_by',
    ];

    protected $appends = ['image_full_url'];

    public function getImageFullUrlAttribute()
    {
        $systemparameters = SystemParameters::where('parameter_name', 'APP_LOGO')->where('status', 1)->first();
        if ($systemparameters && $systemparameters->parameter_value == '') {
            return '';
        } else {
            $local_url = config('values.LOCAL_STORAGE_URL');
            if ($local_url) {
                return $local_url . $this->parameter_value;
            } else {
                return '';
            }
        }
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('parameter_name')
            ->saveSlugsTo('slug');
    }
}
