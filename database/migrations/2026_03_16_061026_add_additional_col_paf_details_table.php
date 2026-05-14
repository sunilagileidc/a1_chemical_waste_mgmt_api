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
        Schema::table('paf_details', function (Blueprint $table) {
            $table->integer('institution_id')->nullable()->after('indication_id')->comment('selected institution id');
            $table->integer('drug_id')->nullable()->after('indication_id')->comment('prescriber id');
            $table->string('patient_category', 100)->nullable()->after('patient_initials')->comment('Patient category');
            $table->string('declaration_name', 100)->nullable()->after('status')->comment('Declaration name');
            $table->date('declaration_date')->nullable()->after('status')->comment('Declaration date');
        });

        Schema::table('paf_drug_cycles', function (Blueprint $table) {
            $table->dropColumn(['declaration_name', 'declaration_date']);
        });

        Schema::table('paf_header', function (Blueprint $table) {
            $table->dropColumn(['drug_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn([
                'institution_id',
                'patient_category',
                'declaration_name',
                'declaration_date',
                'drug_id'
            ]);
        });

        // Restore columns in paf_drug_cycles
        Schema::table('paf_drug_cycles', function (Blueprint $table) {
            $table->string('declaration_name', 100)->nullable();
            $table->date('declaration_date')->nullable();
        });
    }
};
