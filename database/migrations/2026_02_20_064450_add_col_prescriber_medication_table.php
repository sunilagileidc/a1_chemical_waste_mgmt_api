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
            $table->date('start_date')->nullable()->after('is_check')->comment('expiry start date');
            $table->date('end_date')->nullable()->after('start_date')->comment('expiry end date');
            $table->boolean('expired')->default(0)->after('end_date')->comment('Is expired?');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriber_medication', function (Blueprint $table) {
            $table->dropColumn('start_date');
            $table->dropColumn('end_date');
            $table->dropColumn('expired');
        });
    }
};
