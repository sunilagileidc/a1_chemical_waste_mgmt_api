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
        Schema::table('drugs', function (Blueprint $table) {
            $table->text('prescriber_confirmation_text')->after('validity')->nullable()->comment('Prescriber confirmation text');
            $table->text('pharmacist_confirmation_text')->after('prescriber_confirmation_text')->nullable()->comment('pharmacist confirmation text');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drugs', function (Blueprint $table) {
            $table->dropColumn(['prescriber_confirmation_text', 'pharmacist_confirmation_text']);
        });
    }
};
