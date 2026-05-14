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
        Schema::rename('prescriber_drug_molecule', 'paf_drug');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('paf_drug', 'prescriber_drug_molecule');
    }
};
