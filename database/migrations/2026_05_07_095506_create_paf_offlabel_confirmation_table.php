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
        Schema::create('paf_offlabel_confirmation', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->unsignedBigInteger('paf_details_id')->nullable()->comment('PAF details ID');
            $table->string('type', 255)->nullable()->comment('Confirmation type');
            $table->string('confirmation', 500)->nullable()->comment('Confirmation value');
            $table->integer('version')->nullable()->comment('Version number');
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
        Schema::dropIfExists('paf_offlabel_confirmation');
    }
};
