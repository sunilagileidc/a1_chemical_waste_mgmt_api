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
        Schema::create('policy_assigned_questions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->integer('parent_id')->comment('question parent id');
            $table->string('q_type', 250)->nullable()->comment('question type');
            $table->integer('sequence')->default(1)->comment('sequence of the question for ordering');
            $table->string('question', 1000)->nullable()->comment('question');
            $table->string('description', 1000)->nullable()->comment('question description');
            $table->integer('attach_doc')->default(0)->comment('is document attached');
            $table->string('doc_title', 250)->nullable()->comment('document title');
            $table->string('doc_link', 250)->nullable()->comment('document link');
            $table->integer('status')->default(1)->comment('if status is active/inactive');
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
        Schema::dropIfExists('policy_assigned_questions');
    }
};
