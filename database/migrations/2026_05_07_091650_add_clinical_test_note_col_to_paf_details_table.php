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
        Schema::table('paf_details', function (Blueprint $table) {
            $table->text('clinical_test_note')->nullable()->after('is_clinical_trial')->comments('Clinical test note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn('clinical_test_note');
        });
    }
};
