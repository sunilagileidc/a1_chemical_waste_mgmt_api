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
        Schema::create('marketing_holders', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('contact_name', 255)->nullable()->comment('Marketing holders contact name');
            $table->string('contact_email', 100)->nullable()->comment('Marketing holders contact email');
            $table->string('logo', 255)->nullable()->comment('Marketing holders logo');
            $table->text('description')->nullable()->comment('Marketing holders additional notes');
            $table->string('slug', 1000)->nullable()->comment('uniquely generated slug for Marketing holders');
            $table->integer('status')->default(1)->comment('if status is Yes/No');
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
        Schema::dropIfExists('marketing_holders');
    }
};
