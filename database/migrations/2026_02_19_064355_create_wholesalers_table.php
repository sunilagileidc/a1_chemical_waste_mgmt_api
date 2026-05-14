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
        Schema::create('wholesalers', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('name', 255)->nullable()->comment('Name of the wholesaler');
            $table->string('email', 255)->nullable()->comment('Email of the wholesaler');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for wholesaler');
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
        Schema::dropIfExists('wholesalers');
    }
};
