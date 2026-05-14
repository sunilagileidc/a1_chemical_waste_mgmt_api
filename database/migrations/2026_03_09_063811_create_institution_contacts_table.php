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
        Schema::create('institution_contacts', function (Blueprint $table) {
            $table->id();
            $table->integer('institution_id')->nullable()->comment('institution id');
            $table->string('name', 100)->nullable()->comment('Contact name');
            $table->string('email', 100)->nullable()->comment('Contact email');
            $table->integer('status')->default(1)->comment('if status is active/inactive');
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
        Schema::dropIfExists('institution_contacts');
    }
};
