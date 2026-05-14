<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Institutions extends Model
{
    use HasFactory, HasSlug;

    public $table = 'institutions';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'name'
        , 'ref_number'
        , 'institution_type'
        , 'address'
        , 'status'
        , 'slug'
        , 'pharmacy_id'
        , 'post_code'
        , 'ordering_address'
        , 'ordering_post_code'
        , 'delivery_address'
        , 'delivery_post_code'
        , 'created_by'
        , 'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function institution_contacts()
    {
        return $this->hasMany(InstitutionContacts::class, 'institution_id', 'id');
    }

    public function pafDetails()
    {
        return $this->hasMany(PafDetails::class, 'institution_id');
    }
    public function pharmacyDetails()
    {
        return $this->hasMany(PharmacistDetails::class, 'institution_id');
    }
    public function pharmacists()
    {
        return $this->hasMany(PharmacistDetails::class, 'institution_id');
    }
    public function pharmacy()
    {
        return $this->belongsTo(Institutions::class, 'pharmacy_id', 'id');
    }
}
