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
        Schema::table('paf_documents', function (Blueprint $table) {
            $table->string('category', 25)->default('Drugs')->after('status')->comments('for identification purposes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_documents', function (Blueprint $table) {
            $table->dropColumn('category');
        });
    }
};
