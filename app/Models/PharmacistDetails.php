<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PharmacistDetails extends Model
{
    use HasFactory, HasSlug;

    public $table = 'pharmacist_details';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'user_id'
        , 'reg_no'
        , 'phone_no'
        , 'dispensing_address'
        , 'delivery_address'
        , 'delivery_post_code'
        , 'ordering_post_code'
        , 'ordering_address'
        , 'institution_type'
        , 'institution_id'
        , 'reg_status'
        , 'rejection_reason'
        , 'role'
        , 'slug'
        , 'created_by'
        , 'updated_by',
    ];
    protected $appends = ['institution_data'];

    public function institutionsData()
    {
        return $this->hasMany(Institutions::class, 'id', 'institution_id');
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['reg_no', 'phone_no'])
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function getInstitutionDataAttribute()
    {
        return Institutions::with('institution_contacts')->where('id', $this->institution_id)->first(['id', 'name', 'institution_type', 'address']);
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function medications()
    {
        return $this->hasMany(PharmacistMedication::class, 'pharmacist_id');
    }

}
