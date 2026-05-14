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
        Schema::create('institutions', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('name', 255)->nullable()->comment('Name of the institution');
            $table->string('type', 255)->nullable()->comment('Type of the institution');
            $table->text('address', 255)->nullable()->comment('Address of the institution');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for system states');
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
        Schema::dropIfExists('institutions');
    }
};
