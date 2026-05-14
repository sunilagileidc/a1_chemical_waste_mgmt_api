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
        Schema::create('prescriber_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->unsignedBigInteger('user_id')->comment('Foreign key referencing users table');
            $table->string('reg_no',100)->comment('Registration Number');
            $table->string('job_title',250)->comment('Job Title');
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
        Schema::dropIfExists('prescriber_details');
    }
};
