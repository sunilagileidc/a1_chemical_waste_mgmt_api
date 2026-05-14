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
        Schema::create('pharmacist_drug', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('drug_name', 255)->nullable()->comment('Name of the drug');
            $table->date('registration_valid_from')->nullable()->comment('Registration valid From date for pharmacist drug');
            $table->date('registration_valid_to')->nullable()->comment('Registration valid To date for pharmacist drug');
            $table->integer('disabled')->default(0)->comment('Disabled if the record is Inactive or false');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for drug');
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
        Schema::dropIfExists('pharmacist_drug');
    }
};
