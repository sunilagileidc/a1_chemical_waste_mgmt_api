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
            $table->boolean('off_label')->nullable()->default(0)->comment('is off label');
            $table->string('risk_level', 100)->nullable()->comment('risk level');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn('off_label');
            $table->dropColumn('risk_level');
        });

    }
};
