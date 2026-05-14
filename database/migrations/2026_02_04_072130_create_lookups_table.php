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
        Schema::create('lookups', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->integer('parent_id')->default(0)->comment('Parent ID of the Lookup');
            $table->string('shortname', 100)->comment('Shortname of the Lookup');
            $table->string('longname', 500)->comment('Longname of the Lookup');
            $table->string('description', 100)->nullable()->comment('Description or other details');
            $table->integer('seq')->default(1)->comment('Sequence or sorting order for the Lookups');
            $table->string('icon', 100)->nullable()->comment('Icon or Image for the Lookup');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for system lookups');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamps();
        });

        DB::statement("ALTER TABLE `lookups` comment 'This table contains the details of all Lookups available'");

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lookups');
    }
};
