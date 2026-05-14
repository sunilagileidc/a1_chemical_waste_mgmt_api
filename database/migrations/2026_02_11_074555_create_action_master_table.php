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
        Schema::create('action_master', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('action_name', 250)->comment('Action Name');
            $table->string('category', 250)->nullable()->comment('Action Category');
            $table->string('description', 500)->nullable()->comment('Description');
            $table->integer('status')->default(1)->comment('Seeder Status');
            $table->string('slug', 250)->nullable()->comment('Uniquely generated slug for actions');
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
        Schema::dropIfExists('action_master');
    }
};
