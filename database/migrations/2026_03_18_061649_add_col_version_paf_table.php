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
            $table->integer('version')->nullable()->comment('version');
            $table->integer('parent_id')->nullable()->comment('parent');
            
        });

        Schema::table('paf_drug_cycles', function (Blueprint $table) {
            $table->integer('version')->nullable()->comment('version');
            $table->integer('parent_id')->nullable()->comment('parent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('version');
        });

        Schema::table('paf_drug_cycles', function (Blueprint $table) {
            $table->dropColumn('parent_id');
            $table->dropColumn('version');
        });
    }
};
