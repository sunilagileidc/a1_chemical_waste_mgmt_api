<?php
namespace Database\Seeders;

use App\Models\PolicyAssignedQuestions;
use App\Models\PolicyQuestions;
use App\Models\Drugs;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_pharmacist_drug_policies_seeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            /*
|--------------------------------------------------------------------------
| Pharmacy - Lenalidomide Confirmation
|--------------------------------------------------------------------------
*/

            $parentTitle5 = 'Pharmacy Lenalidomide Confirmation';

            $ref_value = Drugs::where('drug_name','Lenalidomide')->value('id');

            $parent5 = PolicyQuestions::where('title', $parentTitle5)
                ->where('linked_to', 'Pharmacist')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent5) {

                $nextSequence5 = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent5 = PolicyQuestions::create([
                    'title'       => $parentTitle5,
                    'description' => null,
                    'linked_to'   => 'Pharmacist',
                    'ref_type'    => 'Drugs',
                    'ref_value'   => $ref_value,
                    'sequence'    => $nextSequence5,
                    'status'      => 1,
                    'created_by'  => 1,
                    'created_at'  => now(),
                ]);
            }

/*
|--------------------------------------------------------------------------
| Child Questions (HTML Format)
|--------------------------------------------------------------------------
*/

            $questions5 = [

                [
                    'question'   => "<p>1. Read and understand the</p>",
                    'attach_doc' => 1,
                    'doc_title'  => 'Lenalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/lenalidomide-guide.pdf',
                ],

                [
                    'question' => "<p>2. Check that each Lenalidomide prescription is provided with an associated Lenalidomide PAF.</p>",
                ],

                [
                    'question' => "<p>3. Check the PAF for completeness and/or request any missing information from the Prescriber and/or patient and complete the Pharmacist section of the PAF, prior to dispensing Lenalidomide.</p>",
                ],

                [
                    'question' => "
            <p>4. For <b>women of childbearing potential (WCBP).</b> Check that the PAF confirms:</p>
            <br><p>a. The WCBP has been counselled/reminded about the teratogenic risk and has been on at least one effective method of contraception for at least 4 weeks.</p>
            <br><p>b. The WCBP has had a negative pregnancy test within the 3 days prior to the prescription date.</p>
            <br><p>c. The dispensing of Lenalidomide is within 7 days of the prescription date.</p>
            <br><p>d. The supply of treatment is no more than 4 weeks.</p>
        ",
                ],

                [
                    'question' => "
            <p>5. For <b>male patients.</b> Check that the PAF confirms:</p>
            <br><p>a. The patient has been counselled/reminded about the teratogenic risk and the requirement to use a condom if sexually active with a pregnant woman or a WCBP not using effective contraception.</p>
            <br><p>b. The supply of treatment is no more than 12 weeks.</p>
        ",
                ],

                [
                    'question' => "
            <p>6. For <b>women not of childbearing potential (WNCRP).</b> Check the supply of treatment is no more than 12 weeks.</p>
        ",
                ],

                [
                    'question' => "
            <p>7. In case of Risk Management Platform unavailability, pharmacists completing offline PAFs must send by fax or email a copy of each completed offline PAF to PharmaCare Group Ltd immediately upon each Lenalidomide prescription being dispensed.</p>
            <p>The original offline PAF should be retained at the pharmacy premises for a minimum of 2 years.</p>
        ",
                ],

                [
                    'question' => "
            <p>8. I acknowledge this registration to complete and approve PAFs and/or order and dispense Lenalidomide is valid for 2 years only, after which I am required to re-register myself, should I wish to continue to complete and approve PAFs and/or dispense Lenalidomide.</p>
        ",
                ],

                [
                    'question' => "
            <p>9. I understand during the period of registration, if I am unable to fulfil all requirements, I will be de-registered from the Risk Management Platform by PharmaCare Group Ltd, and required to go through the registration process again, following any necessary remedial action(s).</p>
        ",
                ],

                [
                    'question' => "
            <p>10. I understand that my personal and professional data will be processed and stored by PharmaCare Group Ltd and Health Beacon Ltd., for the purpose of administering the PPP for Lenalidomide.</p>
        ",
                ],

                [
                    'question' => "
            <p>11. I understand the information supplied to PharmaCare Group Ltd and Health Beacon Ltd through the Risk Management Platform will be used to provide anonymised aggregate annual reports to the Medicines and Healthcare products Regulatory Agency (MHRA) to assess the implementation of the PPP.</p>
            <p>This information may also be used for the purpose of adverse event reporting and follow-up, in compliance with applicable laws and regulations.</p>
        ",
                ],
            ];

            foreach ($questions5 as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent5->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent5->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => trim($data['question']),
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
| Pharmacy 50mg - Thalidomide Confirmation
|--------------------------------------------------------------------------
*/

            $parentTitle = 'Pharmacy 50mg - Thalidomide Confirmation';

            $ref_value = Drugs::where('drug_name','50mg - Thalidomide')->value('id');

            $parent = PolicyQuestions::where('title', $parentTitle)
                ->where('linked_to', 'Pharmacist')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent) {

                $nextSequence = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent = PolicyQuestions::create([
                    'title'       => $parentTitle,
                    'description' => null,
                    'linked_to'   => 'Pharmacist',
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
| Child Questions (HTML Format)
|--------------------------------------------------------------------------
*/

            $questions = [

                [
                    'question' => "<p>By registering to complete and approve PAFs and/or order and dispense 50mg - Thalidomide, I agree to implement and ensure compliance with the risk minimisation measures associated with the Pregnancy Prevention Programme (PPP) for 50mg - Thalidomide and adhere to the following requirements:</p>",
                    'q_type'   => 'Plain Text',
                ],

                [
                    'question'   => "<p>1. Read and understand the</p>",
                    'attach_doc' => 1,
                    'doc_title'  => 'Thalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/thalidomide-guide.pdf',
                ],

                [
                    'question' => "<p>2. Check that each Thalidomide prescription is provided with an associated Thalidomide PAF, completed electronically via the PPP Platform or by using the offline PAF in case of temporary system unavailability.</p>",
                ],

                [
                    'question' => "<p>3. Check the PAF for completeness and/or request any missing information from the Prescriber and/or patient and complete the Pharmacist section of the PAF, prior to dispensing Thalidomide.</p>",
                ],

                [
                    'question' => "
                <p>4. For <b>women of childbearing potential (WCBP).</b> Check that the PAF confirms:</p>
                <br><p>a. The WCBP has been counselled/reminded about the teratogenic risk and has been on at least one effective method of contraception for at least 4 weeks.</p>
                <br><p>b. The WCBP has had a negative pregnancy test within the 3 days prior to the prescription date.</p>
                <br><p>c. The dispensing of Thalidomide is within 7 days of the prescription date.</p>
                <br><p>d. The supply of treatment is no more than 4 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>5. For <b>male patients.</b> Check that the PAF confirms:</p>
                <br><p>a. The patient has been counselled/reminded about the teratogenic risk and the requirement to use a condom if sexually active with a pregnant woman or a WCBP not using effective contraception.</p>
                <br><p>b. The supply of treatment is no more than 12 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>6. For <b>women not of childbearing potential (WNCRP).</b> Check the supply of treatment is no more than 12 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>7. In case of PPP Platform unavailability, pharmacies completing offline PAFs must send a copy of each completed offline PAF to PharmaCare Group Ltd immediately after each Thalidomide prescription is dispensed.</p>
                <p>The original paper PAF should be retained at the pharmacy premises for a minimum of 2 years.</p>
            ",
                ],

                [
                    'question' => "
                <p>8. I acknowledge this registration to complete and approve PAFs and/or order and dispense Thalidomide is valid for 2 years only, after which I am required to re-register myself, should I wish to continue to complete and approve PAFs and/or dispense Thalidomide.</p>
            ",
                ],

                [
                    'question' => "
                <p>9. I understand during the period of registration, if I am unable to fulfil all requirements, I will be de-registered from the Risk Management Platform by PharmaCare Group Ltd, and required to go through the registration process again, following any necessary remedial action(s).</p>
            ",
                ],

                [
                    'question' => "
                <p>10. I understand that my personal and professional data will be processed and stored by PharmaCare Group Ltd and HealthBeacon Ltd., for the purpose of administering the PPP for Thalidomide.</p>
            ",
                ],

                [
                    'question' => "
                <p>11. I understand the information supplied to PharmaCare Group Ltd and Health Beacon Ltd through the Risk Management Platform will be used to provide anonymised aggregate annual reports to the Medicines and Healthcare products Regulatory Agency (MHRA) to assess the implementation of the PPP.</p>
                <p>This information may also be used for the purpose of adverse event reporting and follow-up, in compliance with applicable laws and regulations.</p>
            ",
                ],

            ];

            foreach ($questions as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => trim($data['question']),
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
| Pharmacy Pomalidomide Confirmation
|--------------------------------------------------------------------------
*/

            $parentTitle = 'Pharmacy Pomalidomide Confirmation';

            $ref_value = Drugs::where('drug_name','Pomalidomide')->value('id');

            $parent = PolicyQuestions::where('title', $parentTitle)
                ->where('linked_to', 'Pharmacist')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent) {

                $nextSequence = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent = PolicyQuestions::create([
                    'title'       => $parentTitle,
                    'description' => null,
                    'linked_to'   => 'Pharmacist',
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
| Child Questions (HTML Format)
|--------------------------------------------------------------------------
*/

            $questions = [

                [
                    'question'   => "<p>1. Read and understand the</p>",
                    'attach_doc' => 1,
                    'doc_title'  => 'Pomalidomide Healthcare Professional Information Guide',
                    'doc_link'   => 'https://example.com/pomalidomide-guide.pdf',
                ],

                [
                    'question' => "<p>2. Confirm that all pharmacists who complete and approve PAFs and/or dispense Pomalidomide will have read and understood the Pomalidomide additional Risk Minimisation Materials and will ensure that the pregnancy prevention measures have been implemented before dispensing Pomalidomide.</p>",
                ],

                [
                    'question' => "<p>3. Check that each Pomalidomide prescription is provided with an associated Pomalidomide PAF, completed electronically via the electronic PPP Platform or by using the offline PAF in case of temporary system unavailability.</p>",
                ],

                [
                    'question' => "<p>4. Check the PAF for completeness and/or request any missing information from the Prescriber and/or patient and complete the Pharmacist section of the PAF, prior to dispensing Pomalidomide.</p>",
                ],

                [
                    'question' => "
                <p>5. For <b>women of childbearing potential (WCBP).</b> Check that the PAF confirms:</p>
                <br><p>a. The WCBP has been counselled/reminded about the teratogenic risk and has been on at least one effective method of contraception for at least 4 weeks.</p>
                <br><p>b. The WCBP has had a negative pregnancy test within the 3 days prior to the prescription date.</p>
                <br><p>c. The dispensing of Pomalidomide is within 7 days of the prescription date.</p>
                <br><p>d. The supply of treatment is no more than 4 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>6. For <b>male patients.</b> Check that the PAF confirms:</p>
                <br><p>a. The patient has been counselled/reminded about the teratogenic risk and the requirement to use a condom if sexually active with a pregnant woman or a WCBP not using effective contraception.</p>
                <br><p>b. The supply of treatment is no more than 12 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>7. For <b>women not of childbearing potential (WNCBP).</b> Check the supply of treatment is no more than 12 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>8. In case of PPP Platform unavailability, pharmacies completing offline PAFs must send a copy of each completed offline PAF to PharmaCare Group Ltd immediately after each Pomalidomide prescription is dispensed.</p>
                <p>The original paper PAF should be retained at the pharmacy premises for a minimum of 2 years.</p>
            ",
                ],

                [
                    'question' => "
                <p>9. Ensure on receipt of Pomalidomide, it is only dispensed to the patient by the pharmacy registered with PPP Platform to fulfil the requirements of the PPP for Pomalidomide. <b>Wholesaling is strictly prohibited.</b></p>
            ",
                ],

                [
                    'question' => "
                <p>10. Notify PharmaCare Group Ltd immediately of changes in Chief Pharmacist or appointed Deputy Pharmacist, including their corresponding contact details in order to ensure appropriate registration of the pharmacy to complete and approve PAFs and/or order and dispense Pomalidomide.</p>
            ",
                ],

            ];

            foreach ($questions as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => trim($data['question']),
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

//  | --------------------------------------------------------------------------
//  | Pharmacy100mg - Thalidomide TabletConfirmation
//  | --------------------------------------------------------------------------

            $parentTitle = 'Pharmacy 100mg - Thalidomide Tablet Confirmation';

            $ref_value = Drugs::where('drug_name','100mg - Thalidomide Tablet')->value('id');

            $parent = PolicyQuestions::where('title', $parentTitle)
                ->where('linked_to', 'Pharmacist')
                ->where('ref_type', 'Drugs')
                ->where('ref_value', $ref_value)
                ->first();

            if (! $parent) {

                $nextSequence = (PolicyQuestions::max('sequence') ?? 0) + 1;

                $parent = PolicyQuestions::create([
                    'title'       => $parentTitle,
                    'description' => null,
                    'linked_to'   => 'Pharmacist',
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
| Child Questions (HTML Format)
|--------------------------------------------------------------------------
*/

            $questions = [

                [
                    'question' => "<p>By registering to complete and approve PAFs and/or order and dispense 100mg - Thalidomide, I agree to implement and ensure compliance with the risk minimisation measures associated with the Pregnancy Prevention Programme (PPP) for 100mg - Thalidomide and adhere to the following requirements:</p>",
                    'q_type'   => 'Plain Text',
                ],

                [
                    'question'   => "<p>1. Read and understand the</p>",
                    'attach_doc' => 1,
                    'doc_title'  => 'Thalidomide Healthcare Professional Information Guide 100mg',
                    'doc_link'   => 'https://example.com/thalidomide-100mg-guide.pdf',
                ],

                [
                    'question' => "<p>2. Check that each Thalidomide prescription is provided with an associated Thalidomide PAF, completed electronically via the PPP Platform or by using the offline PAF in case of temporary system unavailability.</p>",
                ],

                [
                    'question' => "<p>3. Check the PAF for completeness and/or request any missing information from the Prescriber and/or patient and complete the Pharmacist section of the PAF, prior to dispensing Thalidomide.</p>",
                ],

                [
                    'question' => "
                <p>4. For <b>women of childbearing potential (WCBP).</b> Check that the PAF confirms:</p>
                <br><p>a. The WCBP has been counselled/reminded about the teratogenic risk and has been on at least one effective method of contraception for at least 4 weeks.</p>
                <br><p>b. The WCBP has had a negative pregnancy test within the 3 days prior to the prescription date.</p>
                <br><p>c. The dispensing of Thalidomide is within 7 days of the prescription date.</p>
                <br><p>d. The supply of treatment is no more than 4 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>5. For <b>male patients.</b> Check that the PAF confirms:</p>
                <br><p>a. The patient has been counselled/reminded about the teratogenic risk and the requirement to use a condom if sexually active with a pregnant woman or a WCBP not using effective contraception.</p>
                <br><p>b. The supply of treatment is no more than 12 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>6. For <b>women not of childbearing potential (WNCBP).</b> Check the supply of treatment is no more than 12 weeks.</p>
            ",
                ],

                [
                    'question' => "
                <p>7. In case of PPP Platform unavailability, pharmacies completing offline PAFs must send a copy of each completed offline PAF to PharmaCare Group Ltd immediately after each Thalidomide prescription is dispensed.</p>
                <p>The original paper PAF should be retained at the pharmacy premises for a minimum of 2 years.</p>
            ",
                ],

                [
                    'question' => "
                <p>8. I acknowledge this registration to complete and approve PAFs and/or order and dispense Thalidomide is valid for 2 years only, after which I am required to re-register myself, should I wish to continue to complete and approve PAFs and/or dispense Thalidomide.</p>
            ",
                ],

                [
                    'question' => "
                <p>9. I understand during the period of registration, if I am unable to fulfil all requirements, I will be de-registered from the Risk Management Platform by PharmaCare Group Ltd, and required to go through the registration process again, following any necessary remedial action(s).</p>
            ",
                ],

                [
                    'question' => "
                <p>10. I understand that my personal and professional data will be processed and stored by PharmaCare Group Ltd and HealthBeacon Ltd., for the purpose of administering the PPP for Thalidomide.</p>
            ",
                ],

                [
                    'question' => "
                <p>11. I understand the information supplied to PharmaCare Group Ltd and Health Beacon Ltd through the Risk Management Platform will be used to provide anonymised aggregate annual reports to the Medicines and Healthcare products Regulatory Agency (MHRA) to assess the implementation of the PPP.</p>
                <p>This information may also be used for the purpose of adverse event reporting and follow-up, in compliance with applicable laws and regulations.</p>
            ",
                ],

            ];

            foreach ($questions as $index => $data) {

                $exists = PolicyAssignedQuestions::where('parent_id', $parent->id)
                    ->where('sequence', $index + 1)
                    ->exists();

                if (! $exists) {
                    PolicyAssignedQuestions::create([
                        'parent_id'   => $parent->id,
                        'q_type'      => $data['q_type'] ?? 'Checkbox',
                        'sequence'    => $index + 1,
                        'question'    => trim($data['question']),
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
