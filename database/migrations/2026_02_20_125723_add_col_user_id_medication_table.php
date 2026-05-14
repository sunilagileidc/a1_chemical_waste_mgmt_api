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
        Schema::table('prescriber_medication', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('drug_id')->comment('Foreign key referencing users table');
        });

        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->after('drug_id')->comment('Foreign key referencing users table');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prescriber_medication', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
        
        Schema::table('pharmacist_medication', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
