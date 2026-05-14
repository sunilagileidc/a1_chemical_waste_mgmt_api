<?php
namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PafDetails extends Model
{
    use HasFactory;

    public $table = 'paf_details';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'paf_no',
        'paf_header_id',
        'patient_dob',
        'patient_initials',
        'last_negative_preg_date',
        'renewal',
        'prescriber_id',
        'indication_id',
        'other_indication',
        'is_clinical_trial',
        'clinical_test_note',
        'institution_id',
        'drug_id',
        'patient_category',
        'declaration_name',
        'declaration_date',
        'status',
        'rejection_reason',
        'revert_reason',
        'is_reviewed',
        'parent_id',
        'version',
        'mah_id',
        'is_inpatient',
        'dispensing_sig',
        'dispensing_date',
        'dispensing_point',
        'dispensing_loc_id',
        'renewal_paf_parent_id',
        'off_label',
        'is_retrospective',
        'risk_level',
        'created_by',
        'updated_by',
    ];

    protected $appends = ['non_conformance', 'is_risk_confirmed'];

    public function indication()
    {
        return $this->belongsTo(Indications::class, 'indication_id')
            ->withDefault(function ($model, $parent) {
                if ($parent->indication_id == 0) {
                    $model->id = 0;

                    //Use dynamic value
                    $model->name = $parent->other_indication
                        ? 'Others (' . $parent->other_indication . ')'
                        : '';

                    $model->description = $parent->other_indication ?? '';
                }
            });
    }
    public function header()
    {
        return $this->belongsTo(PafHeader::class, 'paf_header_id');
    }

    public function drugCycles()
    {
        return $this->hasMany(PafDrugCycle::class, 'paf_details_id', 'id');
    }

    public function drug()
    {
        return $this->belongsTo(Drugs::class, 'drug_id');
    }

    public function prescriber()
    {
        return $this->belongsTo(User::class, 'prescriber_id');
    }
    public function institutions()
    {
        return $this->belongsTo(Institutions::class, 'institution_id');
    }

    public function scopeLatestVersion($query)
    {
        return $query
            ->select('*')
            ->where(function ($q) {
                $q->whereNotNull('version')
                    ->whereIn('version', function ($sub) {
                        $sub->selectRaw('MAX(version)')
                            ->from('paf_details as pd2')
                            ->whereColumn(
                                DB::raw('COALESCE(pd2.parent_id, pd2.id)'),
                                DB::raw('COALESCE(paf_details.parent_id, paf_details.id)')
                            );
                    });
            })
            ->orWhere(function ($q) {
                $q->whereNull('version')
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('paf_details as pd3')
                            ->whereColumn(
                                DB::raw('COALESCE(pd3.parent_id, pd3.id)'),
                                DB::raw('COALESCE(paf_details.parent_id, paf_details.id)')
                            )
                            ->whereNotNull('version');
                    });
            });
    }

    public function mah_data()
    {
        return $this->hasOne(MarketingHolders::class, 'id', 'mah_id');
    }

    public function dispensing_loc()
    {
        return $this->hasOne(Institutions::class, 'id', 'dispensing_loc_id');
    }

    public function nonConformances()
    {
        return $this->hasMany(PAFNonConformance::class, 'paf_details_id', 'id');
    }

    public function getNonConformanceAttribute()
    {
        $pafId = [$this->id];

        return PAFNonConformance::whereIn('paf_details_id', $pafId)
            ->get(['id', 'note']);
    }

    public function getIsRiskConfirmedAttribute()
    {
        $pafIds = [$this->id];

        if ($this->parent_id) {
            $pafIds[] = $this->parent_id;
        }

        return PAFConfirmation::whereIn('paf_detail_id', $pafIds)->latest()
            ->value('is_confirmed');
    }

    public function prescribedDrugCycles()
    {
        return $this->hasMany(PafDrugCycle::class, 'paf_details_id', 'parent_id')
            ->where('version', 1);
    }

    public function getRootIdAttribute()
    {
        return $this->parent_id ?? $this->id;
    }

}
