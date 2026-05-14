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
        Schema::table('connected_pharmacies', function (Blueprint $table) {
            $table->renameColumn('pharmacy_id', 'connected_pharmacy_id');
            $table->integer('user_id')->nullable()->after('connected_pharmacy_id')->comment('user id');
            $table->integer('institution_id')->nullable()->after('user_id')->comment('institution id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('connected_pharmacies', function (Blueprint $table) {
            $table->renameColumn('connected_pharmacy_id', 'pharmacy_id');
            $table->dropColumn(['user_id', 'institution_id']);
        });
    }
};
