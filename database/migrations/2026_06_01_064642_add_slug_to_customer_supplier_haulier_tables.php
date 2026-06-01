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
        Schema::table('customer', function (Blueprint $table) {
            $table->string('slug', 255)
                ->nullable()
                ->after('sic_desc')
                ->comment('Unique slug');
        });

        Schema::table('supplier', function (Blueprint $table) {
            $table->string('slug', 255)
                ->nullable()
                ->after('supplier_license')
                ->comment('Unique slug');
        });

        Schema::table('haulier', function (Blueprint $table) {
            $table->string('slug', 255)
                ->nullable()
                ->after('haulier_license')
                ->comment('Unique slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('supplier', function (Blueprint $table) {
            $table->dropColumn('slug');
        });

        Schema::table('haulier', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};