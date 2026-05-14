<?php

namespace Database\Seeders;

use App\Models\Drugs;
use App\Models\PAFConfirmationText;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class add_confirmation_text_seeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {

            // ---------------------------------------------
            // Fetch Drug IDs
            // ---------------------------------------------
            $lenalidomideId       = Drugs::where('drug_name', 'Lenalidomide')->value('id');
            $thalidomide50Id      = Drugs::where('drug_name', '50mg - Thalidomide')->value('id');
            $thalidomide100Id     = Drugs::where('drug_name', '100mg - Thalidomide Tablet')->value('id');
            $pomalidomideId       = Drugs::where('drug_name', 'Pomalidomide')->value('id');

            // ---------------------------------------------
            // Lenalidomide (3)
            // ---------------------------------------------
            if (!PAFConfirmationText::where('type', 'CONF_LENALIDOMIDE_WCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_LENALIDOMIDE_WCBP',
                    'drug_id' => $lenalidomideId,
                    'patient_category' => 'WCBP',
                    'note' => 'WCBP must follow strict pregnancy prevention and monthly testing.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_LENALIDOMIDE_WNCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_LENALIDOMIDE_WNCBP',
                    'drug_id' => $lenalidomideId,
                    'patient_category' => 'WNCBP',
                    'note' => 'WNCBP patients require standard monitoring and compliance.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_LENALIDOMIDE_MALE')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_LENALIDOMIDE_M',
                    'drug_id' => $lenalidomideId,
                    'patient_category' => 'M',
                    'note' => 'Male patients must follow contraception guidelines.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            // ---------------------------------------------
            // Thalidomide 50mg (3)
            // ---------------------------------------------
            if (!PAFConfirmationText::where('type', 'CONF_THALIDOMIDE50_WCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_THALIDOMIDE50_WCBP',
                    'drug_id' => $thalidomide50Id,
                    'patient_category' => 'WCBP',
                    'note' => 'WCBP must adhere to 1 capsule/day rule and pregnancy checks.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_THALIDOMIDE50_WNCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_THALIDOMIDE50_WNCBP',
                    'drug_id' => $thalidomide50Id,
                    'patient_category' => 'WNCBP',
                    'note' => 'WNCBP must follow dosage compliance and monitoring.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_THALIDOMIDE50_MALE')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_THALIDOMIDE50_M',
                    'drug_id' => $thalidomide50Id,
                    'patient_category' => 'M',
                    'note' => 'Male patients must use protection during treatment.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            // ---------------------------------------------
            // Thalidomide 100mg (3)
            // ---------------------------------------------
            if (!PAFConfirmationText::where('type', 'CONF_THALIDOMIDE100_WCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_THALIDOMIDE100_WCBP',
                    'drug_id' => $thalidomide100Id,
                    'patient_category' => 'WCBP',
                    'note' => 'WCBP requires strict PPP compliance and limited supply.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_THALIDOMIDE100_WNCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_THALIDOMIDE100_WNCBP',
                    'drug_id' => $thalidomide100Id,
                    'patient_category' => 'WNCBP',
                    'note' => 'WNCBP must follow dosage and safety guidelines.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_THALIDOMIDE100_MALE')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_THALIDOMIDE100_M',
                    'drug_id' => $thalidomide100Id,
                    'patient_category' => 'M',
                    'note' => 'Male patients must adhere to safety precautions.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            // ---------------------------------------------
            // Pomalidomide (3)
            // ---------------------------------------------
            if (!PAFConfirmationText::where('type', 'CONF_POMALIDOMIDE_WCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_POMALIDOMIDE_WCBP',
                    'drug_id' => $pomalidomideId,
                    'patient_category' => 'WCBP',
                    'note' => 'WCBP must undergo pregnancy testing and contraception control.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_POMALIDOMIDE_WNCBP')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_POMALIDOMIDE_WNCBP',
                    'drug_id' => $pomalidomideId,
                    'patient_category' => 'WNCBP',
                    'note' => 'WNCBP must follow treatment and monitoring protocols.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

            if (!PAFConfirmationText::where('type', 'CONF_POMALIDOMIDE_MALE')->exists()) {
                PAFConfirmationText::create([
                    'type' => 'CONF_POMALIDOMIDE_M',
                    'drug_id' => $pomalidomideId,
                    'patient_category' => 'M',
                    'note' => 'Male patients must comply with risk minimization measures.',
                    'status' => 1,
                    'created_by' => 1,
                    'updated_by' => 1,
                ]);
            }

        });
    }
}