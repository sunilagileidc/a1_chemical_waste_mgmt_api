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
        Schema::create('login_audit', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('user_id')->comment('Foreign key referencing user table')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45)->nullable()->comment('Country ip address');
            $table->string('country_code', 10)->nullable()->comment('Country code');
            $table->string('country_name')->nullable()->comment('Country name');
            $table->text('user_agent')->nullable()->comment('user agent');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamp('login_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_audit');
    }
};
