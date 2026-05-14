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
        Schema::create('drug_cycles', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('drug_id')->comment('Foreign key referencing drugs table')->constrained('drugs')->cascadeOnDelete();
            $table->integer('lookup_id')->nullable()->comment('Lookup id for cycles name');
            $table->string('cycles_name', 100)->nullable()->comment('Lookup name as cycles name');
            $table->integer('created_by')->nullable()->comment('who created the record');
            $table->integer('updated_by')->nullable()->comment('who last updated the record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_cycles');
    }
};
