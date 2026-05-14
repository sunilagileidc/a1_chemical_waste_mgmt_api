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
            $table->integer('password_count')->default(0)->after('slug')->comment('Password counter for wrong attempt');
            $table->string('is_locked', 2)->default('N')->after('password_count')->comment('Y - user is locked, N - user is unlocked');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_count', 'is_locked');
        });
    }
};
