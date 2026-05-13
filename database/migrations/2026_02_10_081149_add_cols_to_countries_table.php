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
        Schema::table('countries', function (Blueprint $table) {
            $table->string('country_code', 3)->nullable()->after('mobile_code')->comment('ISO 3166-1 alpha-2 country code (e.g., IN)');
            $table->integer('is_whitelisted')->default(1)->after('country_code')->comment('Indicates whether country is whitelisted (1) or blacklisted (0)');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['country_code', 'is_whitelisted']);
        });
    }
};
