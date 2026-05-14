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
        Schema::create('indications', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('name', 255)->nullable()->comment('Indication name');
            $table->text('description')->nullable()->comment('Indications additional notes');
            $table->integer('status')->default(1)->comment('if status is Yes/No');
            $table->string('slug', 1000)->nullable()->comment('uniquely generated slug for indications');
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
        Schema::dropIfExists('indications');
    }
};
