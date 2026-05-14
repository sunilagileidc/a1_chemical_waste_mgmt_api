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
        Schema::create('conformance_rules', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('conformance_type', 250)->nullable()->comment('unique conformance type');
            $table->text('description')->nullable()->comment('Conformance description');
            $table->integer('status')->default(1)->comment('if status is active/inactive');
            $table->string('slug', 500)->nullable()->comment('uniquely generated slug for Conformance');
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
        Schema::dropIfExists('conformance_rules');
    }
};
