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
        Schema::create('other_user_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary key');
            $table->unsignedBigInteger('user_id')->comment('Foreign key referencing users table');
            $table->string('reg_no', 100)->nullable()->comment('Registration Number');
            $table->string('job_title', 250)->nullable()->comment('Job Title');
            $table->unsignedBigInteger('institution_id')->nullable()->comment('Foreign key referencing institutions table');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug');
            $table->unsignedBigInteger('created_by')->nullable()->comment('Who created the record');
            $table->unsignedBigInteger('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('other_user_details', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['institution_id']);
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
        });

        Schema::dropIfExists('other_user_details');
    }
};
