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
        Schema::create('paf_details', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('paf_header_id')
                  ->constrained('paf_header')
                  ->cascadeOnDelete() 
                  ->comment('FK for paf_header.id');
            $table->date('patient_dob')->nullable()->comment('patient date of birth');
            $table->string('patient_initials', 10)->comment('patient initials');
            $table->date('last_negative_preg_date')->nullable()->comment('Last negative pregnancy test date');
            $table->boolean('renewal')->default(false)->comment('Is this a renewal?');
            $table->integer('indication_id')->nullable()->comment('Indication ID');
            $table->string('status', 100)->nullable()->comment('status');
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
        Schema::dropIfExists('paf_details');
    }
};
