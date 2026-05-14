<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Drugs extends Model
{
    use HasFactory, HasSlug;

    public $table = 'drugs';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'drug_name',
        'status',
        'validity',
        'pharmacist_confirmation_text',
        'prescriber_confirmation_text',
        'sequence',
        'drug_form',
        'slug',
        'created_by',
        'updated_by',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('drug_name')
            ->saveSlugsTo('slug');
    }
    // CORRECT RELATIONSHIP
    public function drugStrength()
    {
        return $this->hasMany(Strengths::class, 'drug_id', 'id');
    }
    public function indications()
    {
        return $this->hasMany(DrugIndications::class, 'drug_id', 'id');
    }
    public function cycles()
    {
        return $this->hasMany(DrugCycles::class, 'drug_id', 'id');
    }
    public function capsules()
    {
        return $this->hasMany(DrugCapsules::class, 'drug_id', 'id');
    }
    public function marketing_holders()
    {
        return $this->hasMany(DrugMarketingHolders::class, 'drug_id', 'id');
    }

}
