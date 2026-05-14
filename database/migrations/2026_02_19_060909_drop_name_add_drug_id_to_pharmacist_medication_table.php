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
            $table->dropColumn('name');
            $table->unsignedBigInteger('drug_id')->after('id')->comment('FK of drugs table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->string('name');
            $table->dropColumn('drug_id');
        });
    }
};
