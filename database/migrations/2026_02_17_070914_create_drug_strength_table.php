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
        Schema::create('drug_strength', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('drug_id')->comment('Foreign key referencing drugs table')->constrained('drugs')->cascadeOnDelete();
            $table->string('capsule_strength', 255)->nullable()->comment('Capsule Strength for drug strength');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_strength');
    }
};
