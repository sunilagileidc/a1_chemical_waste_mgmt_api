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
        Schema::table('institutions', function (Blueprint $table) {
            $table->string('ref_number', 250)->nullable()->after('name')->comment('Unique ref number for institute');
            $table->string('contact_name', 250)->nullable()->after('slug')->comment('Contact name');
            $table->string('contact_email', 250)->nullable()->after('slug')->comment('Contact email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn('ref_number');
            $table->dropColumn('contact_name');
            $table->dropColumn('contact_email');
        });
    }
};
