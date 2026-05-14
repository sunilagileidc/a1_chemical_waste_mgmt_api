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
        Schema::create('menus', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('title')->comment('Name of the Menu Title');
            $table->string('icon')->comment('Icon or Image for a Menu')->nullable();
            $table->string('href')->comment('Menu Link to navigate');
            $table->integer('parent_id')->comment('ID of the Parent Menu');
            $table->integer('seq')->comment('Sequence of the menu for Sorting');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for system email template');
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
        Schema::dropIfExists('menus');
    }
};
