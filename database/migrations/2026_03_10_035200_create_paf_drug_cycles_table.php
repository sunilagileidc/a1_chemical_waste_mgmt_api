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
        Schema::create('paf_drug_cycles', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('paf_details_id')
                  ->constrained('paf_details')
                  ->cascadeOnDelete()
                  ->comment('FK paf_details.id');
            $table->string('drug_strength', 100)->nullable()->comment('Drug strength');
            $table->string('cap_per_cycle', 100)->nullable()->comment('Capsules per cycle');
            $table->string('supply_weeks', 100)->nullable()->comment('Supply weeks');
            $table->string('no_of_cycles', 100)->nullable()->comment('Number of cycles');
            $table->string('total_supply', 100)->nullable()->comment('Total supply');
            $table->string('declaration_name', 100)->nullable()->comment('Declaration name');
            $table->date('declaration_date')->nullable()->comment('Declaration date');
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
        Schema::dropIfExists('paf_drug_cycles');
    }
};
