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
        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->integer('version')->nullable()->after('end_date')->comment('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->dropColumn('version');
        });
    }
};