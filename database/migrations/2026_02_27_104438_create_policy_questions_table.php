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
        Schema::create('policy_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('title', 250)->nullable()->comment('question title');
            $table->string('description', 500)->nullable()->comment('question description');
            $table->string('ref_type',100)->nullable()->comment('reference type');
            $table->string('ref_value',100)->nullable()->comment('reference value');
            $table->integer('sequence')->default(1)->comment('sequence of the question for ordering');
            $table->integer('status')->default(1)->comment('status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('uniquely generated slug for drug');
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
        Schema::dropIfExists('policy_questions');
    }
};
