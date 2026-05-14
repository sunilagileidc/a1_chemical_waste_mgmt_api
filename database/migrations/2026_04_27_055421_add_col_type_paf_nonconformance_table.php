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
        Schema::table('paf_nonconformance', function (Blueprint $table) {
            $table->string('type', 500)->nullable()->after('paf_details_id')->comment('nonconformance type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_nonconformance', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
