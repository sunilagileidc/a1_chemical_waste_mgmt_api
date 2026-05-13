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
        Schema::create('database_seeder', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('seeder', 250)->nullable()->comment('Seeder Class');
            $table->string('status')->nullable()->default('Active')->comment('Seeder Status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('database_seeder');
    }
};
