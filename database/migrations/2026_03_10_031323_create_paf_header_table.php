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
        Schema::create('paf_header', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('patient_no', 100)->nullable()->comment('unique patient no');
            $table->string('gender',50)->nullable()->comment('patiend gender');
            $table->integer('prescriber_id')->nullable()->comment('prescriber id');
            $table->integer('drug_id')->nullable()->comment('drug id');
            $table->integer('is_active')->default(1)->comment('if status is active/inactive');
            $table->string('paf_status', 100)->nullable()->comment('paf status');
            $table->string('slug', 500)->nullable()->comment('uniquely generated slug for paf');
            $table->integer('created_by')->nullable()->comment('who created the record');
            $table->integer('updated_by')->nullable()->comment('who last updated the record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paf_header');
    }
};
