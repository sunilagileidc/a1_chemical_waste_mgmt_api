<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class PafDrugCycle extends Model
{
    use HasFactory;

    public $table = 'paf_drug_cycles';

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    protected $fillable = [
        'paf_details_id',
        'drug_strength',
        'cap_per_cycle',
        'supply_weeks',
        'no_of_cycles',
        'total_supply',
        'parent_id',
        'version',
        'created_by',
        'updated_by',
    ];

    public function scopeLatestVersion($query)
    {
        return $query
            ->where(function ($q) {
                $q->whereNotNull('version')
                    ->whereIn('version', function ($sub) {
                        $sub->selectRaw('MAX(version)')
                            ->from('paf_drug_cycles as pc2')
                            ->whereColumn(
                                DB::raw('COALESCE(pc2.parent_id, pc2.id)'),
                                DB::raw('COALESCE(paf_drug_cycles.parent_id, paf_drug_cycles.id)')
                            );
                    });
            })
            ->orWhere(function ($q) {
                $q->whereNull('version')
                    ->whereNotExists(function ($sub) {
                        $sub->select(DB::raw(1))
                            ->from('paf_drug_cycles as pc3')
                            ->whereColumn(
                                DB::raw('COALESCE(pc3.parent_id, pc3.id)'),
                                DB::raw('COALESCE(paf_drug_cycles.parent_id, paf_drug_cycles.id)')
                            )
                            ->whereNotNull('version');
                    });
            });
    }
}
