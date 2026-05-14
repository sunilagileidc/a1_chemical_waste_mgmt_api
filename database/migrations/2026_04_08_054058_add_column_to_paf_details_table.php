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
            $table->integer('is_reviewed')->nullable()->default(0)->after('revert_reason')->comment('is reviewed');
            $table->string('admin_notes', 500)->nullable()->after('is_reviewed')->comment('notes by admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paf_details', function (Blueprint $table) {
            $table->dropColumn('is_reviewed');
            $table->dropColumn('admin_notes');
        });
    }
};
