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
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('rolename', 100)->unique()->comment('Unique Name for a Role');
            $table->string('role_display_name', 100)->comment('Display name for role');
            $table->string('roledescription', 500)->comment('Description of a Role');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for roles');
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
        Schema::dropIfExists('role');
    }
};
