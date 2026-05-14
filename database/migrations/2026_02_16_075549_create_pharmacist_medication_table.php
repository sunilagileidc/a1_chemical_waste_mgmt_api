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
        Schema::create('pharmacist_medication', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->unsignedBigInteger('pharmacist_id')->comment('Foreign key referencing pharmacist_details table');
            $table->string('name', 250)->comment('medication name');
            $table->boolean('is_check')->comment('medication prescribe checkbox');
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
        Schema::dropIfExists('pharmacist_medication');
    }
};
