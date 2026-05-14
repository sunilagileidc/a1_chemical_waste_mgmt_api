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
        Schema::create('paf_nonconformance', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('paf_details_id')
                  ->constrained('paf_details')
                  ->cascadeOnDelete()
                  ->comment('FK paf_details');
            $table->string('note', 500)->nullable()->comment('note');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paf_nonconformance');
    }
};
