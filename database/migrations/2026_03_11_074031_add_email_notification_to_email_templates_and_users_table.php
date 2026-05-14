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
        Schema::table('email_templates', function (Blueprint $table) {
            $table->integer('is_mandatory')->default(1)->comment('Is this email required to be sent to users? Yes/No');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->integer('email_subscription')->default(1)->comment('I agree to receive emails: Yes/No');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            $table->dropColumn('is_mandatory');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_subscription');
        });
    }
};
