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
        Schema::table('prescriber_details', function (Blueprint $table) {
            $table->string('reg_status', 250)->nullable()->after('institution_id')->comment('Registration status');
            $table->string('rejection_reason', 500)->nullable()->after('reg_status')->comment('Reason for rejection');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriber_details', function (Blueprint $table) {
            $table->dropColumn(['reg_status', 'rejection_reason']);
        });
    }
};