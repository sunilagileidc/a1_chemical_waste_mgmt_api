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
        Schema::table('drug_capsules', function (Blueprint $table) {
            $table->renameColumn('capsule_name', 'no_of_capsules');
        });

        Schema::table('drug_cycles', function (Blueprint $table) {
            $table->renameColumn('cycles_name', 'no_of_cycle_weeks');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drug_capsules', function (Blueprint $table) {
            Schema::dropIfExists('no_of_capsules');
        });
        Schema::table('drug_capsules', function (Blueprint $table) {
            Schema::dropIfExists('no_of_cycle_weeks');
        });
    }
};
