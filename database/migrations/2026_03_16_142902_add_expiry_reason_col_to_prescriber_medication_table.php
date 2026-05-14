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
        Schema::table('prescriber_medication', function (Blueprint $table) {
            $table->string('expiry_reason', 255)->nullable()->after('expired')->comment('Drug expiry reason');
        });

        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->string('expiry_reason', 255)->nullable()->after('expired')->comment('Drug expiry reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriber_medication', function (Blueprint $table) {
            $table->dropColumn('expiry_reason');
        });

        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->dropColumn('expiry_reason');
        });
    }
};
