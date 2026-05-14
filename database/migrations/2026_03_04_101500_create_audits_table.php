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
        Schema::create('audits', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->foreignId('user_id')->nullable()->comment('Foreign key referencing user table')->constrained('users')->nullOnDelete();
            $table->string('module', 150)->nullable()->comment('Module name: Orders, Patients, Medicines');
            $table->string('action', 50)->nullable()->comment('Action type: CREATE, UPDATE, DELETE, LOGIN, LOGOUT');
            $table->unsignedBigInteger('reference_id')->nullable()->comment('ID of affected record');
            $table->string('reference_table', 150)->nullable()->comment('Table name of affected entity');
            $table->json('old_values')->nullable()->comment('JSON of old data before update');
            $table->json('new_values')->nullable()->comment('JSON of new data after update');
            $table->json('changed_fields')->nullable()->comment('Only changed fields list');
            $table->string('ip_address', 45)->nullable()->comment('User IP');
            $table->text('user_agent')->nullable()->comment('Browser details');
            $table->string('url', 500)->nullable()->comment('Request URL');
            $table->string('status', 20)->default('SUCCESS')->comment('if status is success/failure');
            $table->text('description')->nullable()->comment('Optional additional notes');
            $table->timestamps();

            // Indexes for performance
            $table->index('module');
            $table->index('action');
            $table->index(['reference_table', 'reference_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audits');
    }
};
