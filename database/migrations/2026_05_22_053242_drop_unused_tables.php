<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        Schema::dropIfExists('connected_pharmacies');
        Schema::dropIfExists('drug_capsules');
        Schema::dropIfExists('drug_cycles');
        Schema::dropIfExists('drug_idications');
        Schema::dropIfExists('drug_marketing_holders');
        Schema::dropIfExists('drug_strength');
        Schema::dropIfExists('indications');
        Schema::dropIfExists('instititions');
        Schema::dropIfExists('institutions_contacts');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('drug_indications');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('institutions');
        Schema::dropIfExists('institution_contacts');
        Schema::dropIfExists('paf_confirmation');
        Schema::dropIfExists('paf_confirmation_text');
        Schema::dropIfExists('paf_details');
        Schema::dropIfExists('paf_documents');
        Schema::dropIfExists('paf_drug');
        Schema::dropIfExists('paf_drug_cycles');
        Schema::dropIfExists('paf_header');
        Schema::dropIfExists('paf_nonconformance');
        Schema::dropIfExists('paf_offlabel_confirmation');
        Schema::dropIfExists('paf_request_information');     
        Schema::dropIfExists('pharmacies');
        Schema::dropIfExists('pharmacist_details');  
        Schema::dropIfExists('pharmacist_drug'); 
        Schema::dropIfExists('pharmacist_medication'); 
        Schema::dropIfExists('pharmacist_wholesaler'); 
        Schema::dropIfExists('policy_assigned_questions'); 
        Schema::dropIfExists('policy_questions');
        Schema::dropIfExists('prescriber_details');
        Schema::dropIfExists('prescriber_medication');
        // Parent table should come last
        Schema::dropIfExists('drugs');

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
