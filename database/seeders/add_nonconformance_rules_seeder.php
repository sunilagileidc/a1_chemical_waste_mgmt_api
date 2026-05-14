<?php
namespace Database\Seeders;

use App\Models\NonConformanceRules;
use Illuminate\Database\Seeder;

class add_nonconformance_rules_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $rules = [

            [
                'conformance_type' => 'INDICATION_REQUIRED',
                'description'      => 'Indication is not selected.',
            ],

            [
                'conformance_type' => 'WCBP_NEG_PREG_INVALID_RANGE',
                'description'      => 'Negative pregnancy test date must be the same day or within 3 days before PAF creation.',
            ],

            [
                'conformance_type' => 'WCBP_MAX_1_CYCLE',
                'description'      => 'For drug compliance, only 1 cycle must be prescribed to WCBP (Woman of Childbearing Potential).',
            ],
            [
                'conformance_type' => 'WCBP_MAX_4_WEEKS_SUPPLY',
                'description'      => 'Supply must not exceed 4 weeks for WCBP (Woman of Childbearing Potential).',
            ],

            // Thalidomide (common)
            [
                'conformance_type' => 'THALIDOMIDE_DOSAGE_RULE',
                'description'      => 'For Thalidomide, dosage must follow 1 capsule per day rule.',
            ],

            // Other values
            [
                'conformance_type' => 'OTHER_INDICATION_REVIEW',
                'description'      => 'Other indication selected. This requires additional review.',
            ],
            [
                'conformance_type' => 'OTHER_REJECTION_REVIEW',
                'description'      => 'PAF rejected due to other reason. This requires additional review.',
            ],
            [
                'conformance_type' => 'OTHER_REVERT_REVIEW',
                'description'      => 'PAF reverted due to other reason. This requires additional review.',
            ],

            // WNCBP
            
            [
                'conformance_type' => 'WNCBP_MAX_12_WEEKS_SUPPLY',
                'description'      => 'For WNCBP patients, maximum allowed supply is 12 weeks.',
            ],

            // Global
            [
                'conformance_type' => 'UNDER_18_OFF_LABEL',
                'description'      => 'Patient is under 18 years old.',
            ],
            [
                'conformance_type' => 'RISK_NOT_CONFIRMED',
                'description'      => 'Risk confirmation is not confirmed.',
            ],
        ];

        foreach ($rules as $rule) {
            NonConformanceRules::create([
                'conformance_type' => $rule['conformance_type'],
                'description'      => $rule['description'],
                'status'           => 1,
                'created_by'       => 1,
                'updated_by'       => 1,
            ]);
        }
    }
}
