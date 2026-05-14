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
            $table->integer('renewal_paf_parent_id')->nullable()->comment('renewal paf parent id');
            $table->boolean('renewal')
              ->comment('is renewed?')
              ->default(false)
              ->nullable()
              ->change();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn('renewal_paf_parent_id');

            $table->boolean('renewal')
              ->default(false)
              ->comment('Is this a renewal?')
              ->change();
        });
    }
};
