<?php
namespace Database\Seeders;

use App\Models\PolicyAssignedQuestions;
use App\Models\PolicyQuestions;
use App\Models\Drugs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_prescriber_drug_policies_seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            /*
            |--------------------------------------------------------------------------
            | Create or Fetch Parent
            |--------------------------------------------------------------------------
            */

            $parentTitle = 'Prescriber Lenalidomide Confirmation';

            $ref_value = Drugs::where('drug_name','Lenalidomide')->value('id');

            $parent = PolicyQuestions::where('title', $parentTitle)
                ->where('linked_to', 'Prescriber')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent) {

                $nextSequence = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent = PolicyQuestions::create([
                    'title'       => $parentTitle,
                    'description' => null,
                    'linked_to'   => 'Prescriber',
                    'ref_type'    => 'Drugs',
                    'ref_value'   => $ref_value,
                    'sequence'    => $nextSequence,
                    'status'      => 1,
                    'created_by'  => 1,
                    'created_at'  => now(),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Child Questions (Fully Customizable)
            |--------------------------------------------------------------------------
            */

            $questions = [

                [
                    'question'   => "1. Read and understand the",
                    'q_type'     => 'Checkbox',
                    'attach_doc' => 1,
                    'doc_title'  => 'Lenalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/lenalidomide-guide.pdf',
                ],

                [
                    'question'   => "2. Comply with all of the Pregnancy Prevention Programme requirements, as defined in the Summary of Product Characteristics and the ",
                    'q_type'     => 'Checkbox',
                    'attach_doc' => 1,
                    'doc_title'  => 'Lenalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/lenalidomide-guide.pdf',
                ],

                [
                    'question' => "3. Complete a 'Prescription Authorisation Form' with each prescription of Lenalidomide",
                ],

                [
                    'question' => "4. Do a medically supervised pregnancy test within the 3 days before the prescription date for women of childbearing potential",
                ],

                [
                    'question' => "5. Prescribe no more than a 4 week supply for women of childbearing potential, and 12 weeks for males and women of non-childbearing potential",
                ],

                [
                    'question' => "6. Confirm that women of childbearing potential have been initially counselled and reminded about the expected teratogenic risk of Lenalidomide and the need to avoid pregnancy",
                ],

                [
                    'question' => "7. Confirm that women of childbearing potential have been on at least one effective method of contraception for at least 4 weeks",
                ],

                [
                    'question' => "8. Confirm that male patients have been initially counselled and reminded about the expected teratogenic risk of Lenalidomide and understand the need to use a condom, if involved in sexual activity with a pregnant woman or a woman of childbearing potential not using effective contraception (even if the male patient has had a vasectomy)",
                ],

                [
                    'question' => "9. I acknowledge this registration to generate and submit PAFs for the prescribing of Lenalidomide is valid for 2 years only, after which I am required to re-register to the Risk Management Platform, should I wish to continue to generate and submit PAFs for Lenalidomide.",
                ],

                [
                    'question' => "10. I understand during the period of registration, if I am unable to fulfil all requirements, I will be de-registered from the Risk Management Platform by PharmaCare Group Ltd, and required to go through the registration process again, following any necessary remedial action(s).",
                ],

                [
                    'question' => "11. I understand that my personal and professional data will be processed and stored by PharmaCare Group Ltd and HealthBeacon Ltd., for the purpose of administering the PPP for Lenalidomide.",
                ],

                [
                    'question' => "12. I understand the information supplied to PharmaCare Group Ltd and HealthBeacon Ltd through the Risk Management Platform will be used to provide anonymised aggregate annual reports to the Medicines and Healthcare products Regulatory Agency (MHRA) to assess the implementation of the PPP. This information may also be used for the purpose of adverse event reporting and follow up, in compliance with applicable laws and regulations.",
                ],
            ];

            /*
            |--------------------------------------------------------------------------
            | Insert Child Questions (Prevent Duplicates)
            |--------------------------------------------------------------------------
            */

            foreach ($questions as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => $data['question'],
                        'description' => null,
                        'attach_doc'  => $data['attach_doc'] ?? 0,
                        'doc_title'   => $data['doc_title'] ?? null,
                        'doc_link'    => $data['doc_link'] ?? null,
                        'status'      => 1,
                        'created_by'  => 1,
                        'created_at'  => now(),
                    ]);
                }
            }

            /*
|--------------------------------------------------------------------------
| Second Parent - Thalidomide 50mg
|--------------------------------------------------------------------------
*/

            $parentTitle2 = 'Prescriber 50mg - Thalidomide Confirmation';

            $ref_value = Drugs::where('drug_name','50mg - Thalidomide')->value('id');

            $parent2 = PolicyQuestions::where('title', $parentTitle2)
                ->where('linked_to', 'Prescriber')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent2) {

                $nextSequence2 = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent2 = PolicyQuestions::create([
                    'title'       => $parentTitle2,
                    'description' => null,
                    'linked_to'   => 'Prescriber',
                    'ref_type'    => 'Drugs',
                    'ref_value'   => $ref_value,
                    'sequence'    => $nextSequence2,
                    'status'      => 1,
                    'created_by'  => 1,
                    'created_at'  => now(),
                ]);
            }

/*
|--------------------------------------------------------------------------
| Child Questions - Thalidomide
|--------------------------------------------------------------------------
*/

            $questions2 = [

                [
                    'question'   => "1. Read and understand the",
                    'q_type'     => 'Checkbox',
                    'attach_doc' => 1,
                    'doc_title'  => 'Thalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/thalidomide-guide.pdf',
                ],

                [
                    'question' => "2. Comply with all of the Pregnancy Prevention Programme requirements, as defined in the Summary of Product Characteristics and the Thalidomide Healthcare Professional Information Guide",
                ],

                [
                    'question' => "3. Complete a 'Prescription Authorisation Form' with each prescription of Thalidomide",
                ],

                [
                    'question' => "4. Do a medically supervised pregnancy test within the 3 days before the prescription date for women of childbearing potential",
                ],

                [
                    'question' => "5. Prescribe no more than a 4 week supply for women of childbearing potential, and 12 weeks for males and women of non-childbearing potential",
                ],

                [
                    'question' => "6. Confirm that women of childbearing potential have been initially counselled and reminded about the expected teratogenic risk of Thalidomide and the need to avoid pregnancy",
                ],

                [
                    'question' => "7. Confirm that women of childbearing potential have been on at least one effective method of contraception for at least 4 weeks",
                ],

                [
                    'question' => "8. Confirm that male patients have been initially counselled and reminded about the expected teratogenic risk of Thalidomide and understand the need to use a condom, if involved in sexual activity with a pregnant woman or a woman of childbearing potential not using effective contraception (even if the male patient has had a vasectomy)",
                ],

                [
                    'question' => "9. I acknowledge this registration to generate and submit PAFs for the prescribing of Thalidomide is valid for 2 years only, after which I am required to re-register to the Risk Management Platform, should I wish to continue to generate and submit PAFs for Thalidomide.",
                ],

                [
                    'question' => "10. I understand during the period of registration, if I am unable to fulfil all requirements, I will be de-registered from the Risk Management Platform by PharmaCare Group Ltd, and required to go through the registration process again, following any necessary remedial action(s).",
                ],

                [
                    'question' => "11. I understand that my personal and professional data will be processed and stored by PharmaCare Group Ltd and HealthBeacon Ltd., for the purpose of administering the PPP for Thalidomide.",
                ],

                [
                    'question' => "12. I understand the information supplied to PharmaCare Group Ltd and HealthBeacon Ltd through the Risk Management Platform will be used to provide anonymized aggregate annual reports to the Medicines and Healthcare products Regulatory Agency (MHRA) to assess the implementation of the PPP. This information may also be used for the purpose of adverse event reporting and follow-up, in compliance with applicable laws and regulations.",
                ],
            ];

            foreach ($questions2 as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent2->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent2->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => $data['question'],
                        'description' => null,
                        'attach_doc'  => $data['attach_doc'] ?? 0,
                        'doc_title'   => $data['doc_title'] ?? null,
                        'doc_link'    => $data['doc_link'] ?? null,
                        'status'      => 1,
                        'created_by'  => 1,
                        'created_at'  => now(),
                    ]);
                }
            }

            /*
|--------------------------------------------------------------------------
| Third Parent - Pomalidomide
|--------------------------------------------------------------------------
*/

            $parentTitle3 = 'Prescriber Pomalidomide Confirmation';

            $ref_value = Drugs::where('drug_name','Pomalidomide')->value('id');

            $parent3 = PolicyQuestions::where('title', $parentTitle3)
                ->where('linked_to', 'Prescriber')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent3) {

                $nextSequence3 = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent3 = PolicyQuestions::create([
                    'title'       => $parentTitle3,
                    'description' => null,
                    'linked_to'   => 'Prescriber',
                    'ref_type'    => 'Drugs',
                    'ref_value'   => $ref_value,
                    'sequence'    => $nextSequence3,
                    'status'      => 1,
                    'created_by'  => 1,
                    'created_at'  => now(),
                ]);
            }

/*
|--------------------------------------------------------------------------
| Child Questions - Pomalidomide
|--------------------------------------------------------------------------
*/

            $questions3 = [

                [
                    'question'   => "1. Read and understand the",
                    'q_type'     => 'Checkbox',
                    'attach_doc' => 1,
                    'doc_title'  => 'Pomalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/pomalidomide-guide.pdf',
                ],

                [
                    'question' => "2. Comply with all of the Pregnancy Prevention Programme requirements, as defined in the Summary of Product Characteristics and the Pomalidomide Healthcare Professional Brochure.",
                ],

                [
                    'question' => "3. Complete a 'Prescription Authorisation Form' with each prescription of Pomalidomide",
                ],

                [
                    'question' => "4. Do a medically supervised pregnancy test within the 3 days before the prescription date for women of childbearing potential",
                ],

                [
                    'question' => "5. Prescribe no more than 4 weeks of therapy for women of childbearing potential and 12 weeks for male patients and women of non-childbearing potential.",
                ],

                [
                    'question' => "6. Confirm that women of childbearing potential have been initially counselled and reminded about the expected teratogenic risk of Pomalidomide and the need to avoid pregnancy",
                ],

                [
                    'question' => "7. Confirm that women of childbearing potential have been on at least one effective method of contraception for at least 4 weeks",
                ],

                [
                    'question' => "8. Confirm that male patients have been initially counselled and reminded about the expected teratogenic risk of Pomalidomide and understand the need to use a condom, if involved in sexual activity with a pregnant woman or a woman of childbearing potential not using effective contraception (even if the male patient has had a vasectomy)",
                ],
            ];

            foreach ($questions3 as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent3->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent3->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => $data['question'],
                        'description' => null,
                        'attach_doc'  => $data['attach_doc'] ?? 0,
                        'doc_title'   => $data['doc_title'] ?? null,
                        'doc_link'    => $data['doc_link'] ?? null,
                        'status'      => 1,
                        'created_by'  => 1,
                        'created_at'  => now(),
                    ]);
                }
            }

            /*
|--------------------------------------------------------------------------
| Fourth Parent - Thalidomide 100mg Tablet
|--------------------------------------------------------------------------
*/

            $parentTitle4 = 'Prescriber 100mg - Thalidomide Tablet Confirmation';

            $ref_value = Drugs::where('drug_name','100mg - Thalidomide Tablet')->value('id');

            $parent4 = PolicyQuestions::where('title', $parentTitle4)
                ->where('linked_to', 'Prescriber')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent4) {

                $nextSequence4 = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent4 = PolicyQuestions::create([
                    'title'       => $parentTitle4,
                    'description' => null,
                    'linked_to'   => 'Prescriber',
                    'ref_type'    => 'Drugs',
                    'ref_value'   => $ref_value,
                    'sequence'    => $nextSequence4,
                    'status'      => 1,
                    'created_by'  => 1,
                    'created_at'  => now(),
                ]);
            }

/*
|--------------------------------------------------------------------------
| Child Questions - Thalidomide 100mg
|--------------------------------------------------------------------------
*/

            $questions4 = [

                // Heading Question
                [
                    'question' => "When prescribing Thalidomide, I accept responsibility to",
                    'q_type'   => 'Plain Text',
                ],

                [
                    'question'   => "1. Read and understand the",
                    'attach_doc' => 1,
                    'doc_title'  => 'Thalidomide Healthcare Professional Information Guide 100mg',
                    'doc_link'   => 'https://example.com/thalidomide-100mg-guide.pdf',
                ],

                [
                    'question'   => "2. Comply with all of the Pregnancy Prevention Programme requirements, as defined in the Summary of Product Characteristics and the",
                    'attach_doc' => 1,
                    'doc_title'  => 'Thalidomide Healthcare Professional Information Guide 100mg',
                    'doc_link'   => 'https://example.com/thalidomide-100mg-guide.pdf',
                ],

                [
                    'question' => "3. Complete a 'Prescription Authorisation Form' with each prescription of Thalidomide",
                ],

                [
                    'question' => "4. Do a medically supervised pregnancy test within the 3 days before the prescription date for women of childbearing potential",
                ],

                [
                    'question' => "5. Prescribe no more than a 4 week supply for women of childbearing potential, and 12 weeks for males and women of non-childbearing potential",
                ],

                [
                    'question' => "6. Confirm that women of childbearing potential have been initially counselled and reminded about the expected teratogenic risk of Thalidomide and the need to avoid pregnancy",
                ],

                [
                    'question' => "7. Confirm that women of childbearing potential have been on at least one effective method of contraception for at least 4 weeks",
                ],

                [
                    'question' => "8. Confirm that male patients have been initially counselled and reminded about the expected teratogenic risk of Thalidomide and understand the need to use a condom, if involved in sexual activity with a pregnant woman or a woman of childbearing potential not using effective contraception (even if the male patient has had a vasectomy)",
                ],

                [
                    'question' => "9. I acknowledge this registration to generate and submit PAFs for the prescribing of Thalidomide is valid for 2 years only, after which I am required to re-register to the Risk Management Platform, should I wish to continue to generate and submit PAFs for Thalidomide.",
                ],

                [
                    'question' => "10. I understand during the period of registration, if I am unable to fulfil all requirements, I will be de-registered from the Risk Management Platform by PharmaCare Group Ltd, and required to go through the registration process again, following any necessary remedial action(s).",
                ],

                [
                    'question' => "11. I understand that my personal and professional data will be processed and stored by PharmaCare Group Ltd and HealthBeacon Ltd., for the purpose of administering the PPP for Thalidomide.",
                ],

                [
                    'question' => "12. I understand the information supplied to PharmaCare Group Ltd and HealthBeacon Ltd through the Risk Management Platform will be used to provide anonymized aggregate annual reports to the Medicines and Healthcare products Regulatory Agency (MHRA) to assess the implementation of the PPP. This information may also be used for the purpose of adverse event reporting and follow up, in compliance with applicable laws and regulations.",
                ],
            ];

            foreach ($questions4 as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent4->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent4->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => $data['question'],
                        'description' => null,
                        'attach_doc'  => $data['attach_doc'] ?? 0,
                        'doc_title'   => $data['doc_title'] ?? null,
                        'doc_link'    => $data['doc_link'] ?? null,
                        'status'      => 1,
                        'created_by'  => 1,
                        'created_at'  => now(),
                    ]);
                }
            }

            PolicyQuestions::all()->each(function ($item) {
                $item->slug = null;
                $item->save();
            });

            PolicyAssignedQuestions::all()->each(function ($item) {
                $item->slug = null;
                $item->save();
            });

        });
    }
}
