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
        Schema::create('sales_invoices', function (Blueprint $table) {

            $table->bigIncrements('id')->comment('Primary Key');
            $table->string('invoice_number')  ->unique();
            $table->unsignedBigInteger('sales_quotation_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->string('po_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->date('due_date')->nullable();
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('vat', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->enum('status',['draft','sent','paid','cancelled', ]) ->default('draft');
            $table->boolean('active')->default(1);
            $table->string('slug') ->unique();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('sales_quotation_id')
                ->references('id')
                ->on('sales_quotations')
                ->cascadeOnDelete();
            $table->foreign('customer_id')
                ->references('id')
                ->on('customer')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_invoices');
    }
};
