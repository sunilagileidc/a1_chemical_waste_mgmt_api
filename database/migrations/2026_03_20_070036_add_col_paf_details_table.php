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
        Schema::table('paf_details', function (Blueprint $table) {
            $table->integer('mah_id')->nullable()->comment('marketing holders id');
            $table->integer('is_inpatient')->nullable()->comment('is inpatient?');
            $table->string('dispensing_sig', 100)->nullable()->comment('dispensing signature');
            $table->date('dispensing_date')->nullable()->comment('dispensing date');
            $table->string('dispensing_point',100)->nullable()->comment('dispensing point');
            $table->integer('dispensing_loc_id')->nullable()->comment('dispensing location id (institution id)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn('mah_id');
            $table->dropColumn('is_inpatient');
            $table->dropColumn('dispensing_sig');
            $table->dropColumn('dispensing_date');
            $table->dropColumn('dispensing_point');
            $table->dropColumn('dispensing_loc_id');
        });
    }
};
