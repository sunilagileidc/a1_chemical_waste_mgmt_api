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
        Schema::create('pharmacist_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->unsignedBigInteger('user_id')->comment('Foreign key referencing users table');
            $table->string('reg_no',100)->comment('GPhC / PSNI  Registration Number');
            $table->string('phone_no',100)->nullable()->comment('phone number');
            $table->string('dispensing_address',500)->nullable()->comment('dispensing pharmacy address');
            $table->string('delivery_address',500)->nullable()->comment('delivery address');
            $table->string('ordering_address',500)->nullable()->comment('ordering address');
            $table->string('institution_type',250)->comment('institution type');
            $table->unsignedBigInteger('institution_id')->comment('Foreign key institutions table');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug');
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
        Schema::dropIfExists('pharmacist_details');
    }
};
