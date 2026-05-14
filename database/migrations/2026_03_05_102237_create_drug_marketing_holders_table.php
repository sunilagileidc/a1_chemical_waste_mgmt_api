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
        Schema::create('drug_marketing_holders', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('drug_id')->comment('Foreign key referencing drugs table')->constrained('drugs')->cascadeOnDelete();
            $table->foreignId('marketing_holder_id')->comment('Foreign key referencing marketing_holders table')->constrained('marketing_holders')->cascadeOnDelete();
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
        Schema::dropIfExists('drug_marketing_holders');
    }
};
