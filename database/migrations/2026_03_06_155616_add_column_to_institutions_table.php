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
        Schema::table('institutions', function (Blueprint $table) {
            $table->integer('pharmacy_id')->nullable()->after('ref_number')->comment('pharmacy id for institutions');
            $table->string('institution_type', 255)->nullable()->after('pharmacy_id')->comment('institution_type of institution');
            $table->string('post_code', 255)->nullable()->after('institution_type')->comment('Postcode of the institution');
            $table->text('ordering_address')->nullable()->after('address')->comment('Ordering Address of the institution');
            $table->text('delivery_address')->nullable()->after('ordering_address')->comment('Delivery Address of the institution');
            $table->dropColumn(['type', 'contact_email', 'contact_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn(['pharmacy_id', 'institution_type', 'post_code', 'ordering_address', 'delivery_address']);

            $table->string('type')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_name')->nullable();
        });
    }
};
