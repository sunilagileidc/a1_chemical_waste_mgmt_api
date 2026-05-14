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
        Schema::table('documents', function (Blueprint $table) {
            $table->integer('is_re_registration')->default(0)->after('doc_version')->comment('Is registering again');
            $table->integer('is_downloaded')->default(0)->after('is_re_registration')->comment('Is downloaded or not');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['is_re_registration', 'is_downloaded']);
        });
    }
};
