<?php
namespace Database\Seeders;

use App\Models\PAFConfirmationText;
use Illuminate\Database\Seeder;

class add_confirm_text_paf_declaration_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PAFConfirmationText::create([
            'type'       => 'PAF_DECLARATION',
            'note'       => 'I confirm that I am a prescriber experienced in managing haematological malignancy and I have read and understood the Healthcare Professional\'s Information Guide and confirm that the patient has signed a Risk Awareness Form.',
            'status'     => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
        PAFConfirmationText::create([
            'type'       => 'PAF_UNDER_18',
            'note'       => 'I confirm that the patient is under 18 years of age.',
            'status'     => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
        PAFConfirmationText::create([
            'type'       => 'PAF_OFF_LABEL_USE',
            'note'       => 'I can confirm I intend to use ${drugName} for an off-label use (${indication}), and that this treatment will be continuing.',
            'status'     => 1,
            'created_by' => 1,
            'updated_by' => 1,
        ]);
    }
}
