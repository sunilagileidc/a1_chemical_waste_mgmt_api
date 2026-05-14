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
        Schema::table('pharmacist_wholesaler', function (Blueprint $table) {
            $table->dropColumn('name');
            $table->unsignedBigInteger('wholesaler_id')->after('id')->comment('FK of wholesaler table');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacist_wholesaler', function (Blueprint $table) {
            $table->dropColumn('wholesaler_id');
            $table->string('name');
        });
    }
};
