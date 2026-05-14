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
        Schema::table('policy_questions', function (Blueprint $table) {
             $table->string('linked_to')->after('description')->nullable()->comment('link to a particular group');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_questions', function (Blueprint $table) {
            $table->dropColumn('linked_to');
        });
    }
};
