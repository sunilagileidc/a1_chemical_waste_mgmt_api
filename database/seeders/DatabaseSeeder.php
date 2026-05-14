<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{

    /**
     * Created By Stalvin
     * Using from 10-02-2026
     * Global database seeder runner
     * @return void
     */

    public function run(): void
    {
        $this->runSeeder(add_superuser_seeder::class);
        $this->runSeeder(add_menu_dashboard::class);
        $this->runSeeder(add_role_seeder::class);
        $this->runSeeder(superuser_menu_seeder::class);
        $this->runSeeder(add_basic_lookups_seeder::class);
        $this->runSeeder(add_email_templates_seeder::class);
        $this->runSeeder(add_location_seeder::class);
        $this->runSeeder(add_system_parameter_basic_data::class);
        // $this->runSeeder(add_institutions_data_seeder::class);
        $this->runSeeder(add_group_to_lookup_seeder::class);
        $this->runSeeder(add_drug_menu_seeder::class);
        $this->runSeeder(add_institution_type_lookup_seeder::class);
        $this->runSeeder(add_drugs_strength_data_seeder::class);
        $this->runSeeder(add_wholesaler_menu_seeder::class);
        $this->runSeeder(add_lookup_jobtitles_seeder::class);
        $this->runSeeder(add_lov_drugs_seeder::class);
        // $this->runSeeder(add_lov_wholesalers_seeder::class);
        $this->runSeeder(add_appr_rej_email_template_seeder::class);
        $this->runSeeder(add_session_timeout_duration_to_system_parameter::class);
        $this->runSeeder(add_admin_roles_seeder::class);
        $this->runSeeder(add_menu_policiesandquestions_seeder::class);
        $this->runSeeder(add_lookup_question_category_seeder::class);
        $this->runSeeder(add_prescriber_drug_policies_seeder::class);
        $this->runSeeder(add_pharmacist_drug_policies_seeder::class);
        $this->runSeeder(add_user_lock_attempt_limit_system_parameter_seeder::class);
        $this->runSeeder(add_indications_menu_seeder::class);
        $this->runSeeder(add_capsules_lookup_seeder::class);
        $this->runSeeder(add_cycles_lookup_seeder::class);
        $this->runSeeder(add_audit_menu_seeder::class);
        $this->runSeeder(add_registration_expiry_email_template_seeder::class);
        $this->runSeeder(update_drug_strength_shortname_seeder::class);
        $this->runSeeder(add_hospital_inside_instittion_type_seeder::class);
        $this->runSeeder(add_pharmacies_menu_seeder::class);
        $this->runSeeder(add_menu_user_dashboard_seeder::class);
        $this->runSeeder(add_locked_user_menu_seeder::class);
        $this->runSeeder(add_rejected_reasons_lookup_seeder::class);
        $this->runSeeder(add_document_group_lookup_seeder::class);
        $this->runSeeder(add_general_group_lookup_seeder::class);
        $this->runSeeder(add_expired_email_template::class);
        $this->runSeeder(add_paf_rejection_reason_seeder::class);
        $this->runSeeder(add_num_off_cycles_lookup_seeder::class);
        $this->runSeeder(update_registration_status_seeder::class);
        $this->runSeeder(add_forced_re_registration_emailtemplate_seeder::class);
        $this->runSeeder(add_action_master_seeder::class);
        $this->runSeeder(add_action_master_values_seeder::class);
        $this->runSeeder(add_option_action_categories_seeder::class);
        $this->runSeeder(add_action_master_add_new_paf_values_seeder::class);
        $this->runSeeder(add_action_master_editpafdrugcycle_seeder::class);
        $this->runSeeder(add_action_master_revert_paf_seeder::class);
        $this->runSeeder(add_lookup_paf_revert_reasons_seeder::class);
        $this->runSeeder(add_action_master_registerbtn_myprofile_addhospital_seeder::class);
        $this->runSeeder(add_paf_menu_seeder::class);
        $this->runSeeder(add_action_master_btns_seeder::class);
        $this->runSeeder(add_action_master_review_paf_btn_seeder::class);
        $this->runSeeder(add_overdue_time_to_system_paramter_seeder::class);
        $this->runSeeder(add_action_master_merge_paf_seeder::class);
        $this->runSeeder(add_is_suspicious_actor_action_master_seeder::class);
        $this->runSeeder(add_menu_reports_seeder::class);
        $this->runSeeder(add_action_master_request_paf_btn_seeder::class);
        $this->runSeeder(add_paf_request_information_email_template::class);
        $this->runSeeder(add_menu_wholesaler_report_seeder::class);
        $this->runSeeder(add_off_label_usage_email_template_seeder::class);
        $this->runSeeder(add_action_master_dispense_op_paf_seeder::class);
        $this->runSeeder(add_paf_daily_alert_email_seeder::class);
        $this->runSeeder(add_paf_daily_alert_report_email_template::class);
        $this->runSeeder(alter_paf_menu_seeder::class);
        $this->runSeeder(add_wholesaler_dashboard_menu_seeder::class);
        $this->runSeeder(add_action_master_mark_nonconformance_seeder::class);
        $this->runSeeder(add_review_pafs_report_menu_seeder::class);
        $this->runSeeder(add_download_alert_email_template_seeder::class);
        $this->runSeeder(add_confirmation_text_seeder::class);
        $this->runSeeder(add_action_master_add_drug_strength_seeder::class);
        $this->runSeeder(add_wholesaler_role_seeder::class);
        $this->runSeeder(add_non_conformance_rules_menu_seeder::class);
        $this->runSeeder(add_nonconformance_rules_seeder::class);
        $this->runSeeder(add_paf_confirmation_text_menu_seeder::class);
        $this->runSeeder(add_paf_documents_menu_seeder::class);
        $this->runSeeder(add_supplier_sales_data_menu_seeder::class);
        $this->runSeeder(add_drug_form_types_lookup_seeder::class);
        $this->runSeeder(add_confirm_text_paf_declaration_seeder::class);
        $this->runSeeder(add_settings_menu_seeder::class);
    }

    /**
     * Run the given seeder only if it has not been executed before.
     * @param  string  $seederClass
     * @return void
     */

    protected function runSeeder(string $seederClass)
    {
        $className       = class_basename($seederClass);
        $alreadyExecuted = DB::table('database_seeder')
            ->where('seeder', $className)
            ->where('status', 1)
            ->exists();
        if ($alreadyExecuted) {
            return;
        } else {
            $this->call($seederClass);
            DB::table('database_seeder')->insert([
                'seeder' => $className,
                'status' => 1,
            ]);
        }
    }
}
