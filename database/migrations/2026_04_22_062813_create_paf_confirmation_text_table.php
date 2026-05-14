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
        Schema::create('paf_confirmation_text', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('type', 250)->unique()->nullable()->comment('unique confirmation type');
            $table->integer('drug_id')->nullable()->comment('drugs table fk');
            $table->string('patient_category',50)->nullable()->comment('patient category');
            $table->string('note', 500)->nullable()->comment('note');
            $table->boolean('status')->default(1);
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
        Schema::dropIfExists('paf_confirmation_text');
    }
};
