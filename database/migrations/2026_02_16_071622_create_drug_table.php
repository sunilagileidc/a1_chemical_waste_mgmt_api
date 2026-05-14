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
        Schema::create('drug', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('drug_name', 255)->nullable()->comment('Name of the drug');
            $table->string('capsule_strength', 255)->nullable()->comment('Capsule Strength for drug');
            $table->integer('capsules_per_cyle')->nullable()->comment('Capsules per Cyle for drug');
            $table->integer('number_of_cycles')->nullable()->comment('Number of Cycles for drug');
            $table->integer('total_capsules')->nullable()->comment('total capsules for  a drug');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for drug');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug');
    }
};
