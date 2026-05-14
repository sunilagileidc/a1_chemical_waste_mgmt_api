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
        Schema::table('pharmacist_details', function (Blueprint $table) {
            $table->text('ordering_post_code')->nullable()->after('ordering_address')->comments('ordering postcode');
            $table->text('delivery_post_code')->nullable()->after('delivery_address')->comments('delivery postcode');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pharmacist_details', function (Blueprint $table) {
            $table->dropColumn(['ordering_post_code', 'ordering_post_code']);
        });
    }
};
