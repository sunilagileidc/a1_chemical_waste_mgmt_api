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
        Schema::create('paf_request_information', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->unsignedBigInteger('paf_detail_id')->nullable()->comment('foreign key'); // FK
            $table->text('request_note')->nullable()->comment('Request note from admin');
            $table->json('requested_users')->nullable()->comment('Requested user list'); // store selected user IDs
            $table->integer('reminder_count')->default(0)->comment('Reminder count');
            $table->timestamp('last_reminder_sent_at')->nullable();
            $table->string('paf_no')->nullable();
            $table->string('patient_id')->nullable();
            $table->boolean('is_closed')->default(0);
            $table->integer('created_by')->nullable()->comment('who created the record');
            $table->integer('updated_by')->nullable()->comment('who last updated the record');
            $table->timestamps();

            $table->foreign('paf_detail_id')->references('id')->on('paf_details')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paf_request_information');
    }
};
