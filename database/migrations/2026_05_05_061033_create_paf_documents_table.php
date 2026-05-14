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
        Schema::create('paf_documents', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('title')->nullable()->comment('Title for documents');
            $table->unsignedBigInteger('parent_id')->nullable()->comment('Parent document id for versioning');
            $table->text('description')->nullable()->comment('Description for documents');
            $table->string('file_name')->nullable()->comment('Document name');
            $table->string('file_path')->nullable()->comment('Document path');
            $table->string('file_type')->nullable()->comment('Document type');
            $table->string('mime')->nullable()->comment('mime type');
            $table->bigInteger('file_size')->nullable()->comment('Document size');
            $table->string('patient_category')->nullable()->comments('patient category for identification purposes');
            $table->integer('drug_id')->nullable()->comment('drug id for documents');
            $table->string('group')->nullable()->comments('for identification purposes');
            $table->integer('sequence')->nullable()->comment('Sequence for documents');
            $table->string('doc_version', 10)->default('v1')->comment('Document version');
            $table->integer('is_re_registration')->default(0)->comment('Is registering again');
            $table->integer('is_downloaded')->default(0)->comment('Is downloaded or not');
            $table->integer('is_training_document')->default(0)->comment('Is this training documnet');
            $table->integer('download_alert')->default(0)->comment('Determines if a download alert email is sent to admin');
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
        Schema::dropIfExists('paf_documents');
    }
};
