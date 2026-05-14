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
            $table->integer('prescriber_id')->nullable()->after('indication_id')->comment('prescriber id');
        });

        Schema::table('paf_header', function (Blueprint $table) {
            $table->dropColumn(['prescriber_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn(['prescriber_id']);
        });

        Schema::table('paf_header', function (Blueprint $table) {
            $table->integer('prescriber_id')->nullable()->after('indication_id')->comment('prescriber id');
        });
    }
};
