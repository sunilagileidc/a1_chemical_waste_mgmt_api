<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('sales_quotations', function (Blueprint $table) {

            $table->bigIncrements('id')->comment('Primary Key');
            $table->string('quotation_number')->unique();
            $table->string('job_name')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->date('quotation_date')->nullable();
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->decimal('document_cost', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->boolean('quote_saved')->default(0);
            $table->boolean('quote_finalised')->default(0);
            $table->enum('status', ['draft', 'finalised', 'cancelled'])->default('draft');
            $table->string('slug')->unique();
            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('customer_id')
                ->references('id')
                ->on('customer')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_quotations');
    }
};
