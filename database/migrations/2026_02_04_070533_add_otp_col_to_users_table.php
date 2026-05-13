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
        Schema::table('users', function (Blueprint $table) {
            $table->string('otp', 100)->comment('Generated otp for recover password')->nullable()->after('mobile_code');
            $table->dateTime('otp_valid_until')->comment('Generated otp expire date and time')->nullable('otp');
            $table->integer('is_otp_validated')->default(1)->comment('Is the OTP Validated?')->nullable('otp_valid_until');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['otp', 'otp_valid_until', 'is_otp_validated']);
        });
    }
};
