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
        Schema::table('documents', function (Blueprint $table) {
            $table->string('doc_version', 10)->default('v1')->after('sequence')->comment('Document version');
            $table->unsignedBigInteger('parent_id')->nullable()->after('title')->comment('Parent document id for versioning');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn(['doc_version', 'parent_id']);
        });
    }
};
