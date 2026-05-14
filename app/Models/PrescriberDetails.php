<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PrescriberDetails extends Model
{
    use HasFactory, HasSlug;

    public $table = 'prescriber_details';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id'
        , 'reg_no'
        , 'job_title'
        , 'institution_id'
        , 'reg_status'
        , 'rejection_reason'
        , 'slug'
        , 'created_by'
        , 'updated_by',
    ];
    protected $appends = ['institution_data'];
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['reg_no', 'job_title'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }
    public function institutions()
    {
        return $this->belongsTo('App\Models\Institutions', 'institution_id', 'id');
    }
    public function getInstitutionDataAttribute()
    {
        $institution = Institutions::where('id', $this->institution_id)
            ->first(['id', 'name', 'institution_type', 'address', 'pharmacy_id']);

        if (!$institution) {
            return null;
        }

        // Get parent (pharmacy) name
        $pharmacyName = null;

        if ($institution->pharmacy_id) {
            $parent = Institutions::where('id', $institution->pharmacy_id)
                ->first(['name']);

            $pharmacyName = $parent ? $parent->name : null;
        }

        // Append new field
        $institution->pharmacy_name = $pharmacyName;

        return $institution;
    }

}
