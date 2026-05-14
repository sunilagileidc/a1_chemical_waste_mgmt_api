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
        Schema::create('documents', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('title')->nullable()->comment('Title for documents');
            $table->text('description')->nullable()->comment('Description for documents');
            $table->string('file_name')->nullable()->comment('Document name');
            $table->string('file_path')->nullable()->comment('Document path');
            $table->string('file_type')->nullable()->comment('Document type');
            $table->string('mime')->nullable()->comment('mime type');
            $table->bigInteger('file_size')->nullable()->comment('Document size');
            $table->string('category')->nullable()->comments('for identification purposes');   // order, kyc, invoice, etc
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for documents');
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
        Schema::dropIfExists('documents');
    }
};
