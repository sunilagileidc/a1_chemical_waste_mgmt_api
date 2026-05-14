<?php
namespace App\Models;

use DB;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasSlug;

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'password',
        'token_id',
        'role_id',
        'salutation',
        'gender',
        'dob',
        'address',
        'postcode',
        'description',
        'image_url',
        'country',
        'state',
        'city',
        'mobile',
        'mobile_code',
        'otp',
        'otp_valid_until',
        'is_otp_validated',
        'status',
        'reg_status',
        'rejection_reason',
        'slug',
        'expired',
        'password_count',
        'is_locked',
        'signature',
        'email_subscription',
        'is_suspicious_actor',
        'signature_date',
        'created_by',
        'updated_by',
    ];

    protected $hidden = [
        // 'password',
        'remember_token',
    ];

    protected $appends = ['rolename', 'full_name', 'created_user'];

    public function getFullNameAttribute()
    {

        if ($this->name) {
            return $this->name . " " . $this->lastname;
        } else {
            return '';
        }
    }

    public function getRoleNameAttribute()
    {
        $roledata = Role::where('id', $this->role_id)->first();
        if ($roledata) {
            return $roledata->rolename;
        } else {
            return '';
        }
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
    public function role()
    {
        return $this->belongsTo('App\Models\Role', 'role_id', 'id');
    }
    public function country()
    {
        return $this->belongsTo('App\Models\Countries', 'country', 'id');
    }
    public function state()
    {
        return $this->belongsTo('App\Models\States', 'state', 'id');
    }
    public function city()
    {
        return $this->belongsTo('App\Models\Cities', 'city', 'id');
    }
    public function pharmacist()
    {
        return $this->belongsTo('App\Models\Pharmacist', 'id', 'user_id');
    }

    public function prescriber_data()
    {
        return $this->hasOne(PrescriberDetails::class, 'user_id');
    }

    public function prescriberMedications()
    {

        return $this->hasMany(PrescriberMedication::class, 'user_id')

            ->where(function ($main) {

                // Case 1: version exists → get MAX(version) per user + drug
                $main->where(function ($q) {
                    $q->whereNotNull('version')
                        ->whereIn('version', function ($sub) {
                            $sub->selectRaw('MAX(pm2.version)')
                                ->from('prescriber_medication as pm2')
                                ->whereColumn('pm2.user_id', 'prescriber_medication.user_id')
                                ->whereColumn('pm2.drug_id', 'prescriber_medication.drug_id');
                        });
                })

                // Case 2: version NULL → only if no versioned record exists
                    ->orWhere(function ($q) {
                        $q->whereNull('version')
                            ->whereNotExists(function ($sub) {
                                $sub->select(DB::raw(1))
                                    ->from('prescriber_medication as pm3')
                                    ->whereColumn('pm3.user_id', 'prescriber_medication.user_id')
                                    ->whereColumn('pm3.drug_id', 'prescriber_medication.drug_id')
                                    ->whereNotNull('pm3.version');
                            });
                    });

            });

    }

    public function pharmacistDetails()
    {
        return $this->hasMany(PharmacistDetails::class, 'user_id');
    }

    public function pharmacistMedications()
    {
        return $this->hasMany(PharmacistMedication::class, 'user_id')
            ->where(function ($main) {

                // Case 1: version exists → get MAX(version)
                $main->where(function ($q) {
                    $q->whereNotNull('version')
                        ->whereIn('version', function ($sub) {
                            $sub->selectRaw('MAX(pm2.version)')
                                ->from('pharmacist_medication as pm2')
                                ->whereColumn('pm2.user_id', 'pharmacist_medication.user_id')
                                ->whereColumn('pm2.drug_id', 'pharmacist_medication.drug_id');
                        });
                })

                // Case 2: version NULL → only if no versioned record exists
                    ->orWhere(function ($q) {
                        $q->whereNull('version')
                            ->whereNotExists(function ($sub) {
                                $sub->select(DB::raw(1))
                                    ->from('pharmacist_medication as pm3')
                                    ->whereColumn('pm3.user_id', 'pharmacist_medication.user_id')
                                    ->whereColumn('pm3.drug_id', 'pharmacist_medication.drug_id')
                                    ->whereNotNull('pm3.version');
                            });
                    });

            });
    }

    public function prescriberDetails()
    {
        return $this->hasMany(PrescriberDetails::class, 'user_id');
    }
    public function pharmacistData()
    {
        return $this->hasOne(PharmacistDetails::class, 'user_id');
    }
    public function otherUserDetails()
    {
        return $this->hasMany(\App\Models\OtherUserDetails::class, 'user_id', 'id');
    }
    public function getCreatedUserAttribute()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by')->first();
    }
    public function connectedNurses()
    {
        return $this->hasMany(\App\Models\User::class, 'created_by', 'id');
    }

}
