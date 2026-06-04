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
        Schema::create('quotation_items', function (Blueprint $table) {

            $table->bigIncrements('id')->comment('Primary Key');
            $table->unsignedBigInteger('sales_quotation_id')->nullable();
            $table->unsignedBigInteger('waste_stream_id')->nullable();
            $table->integer('item_order')->default(0);
            $table->string('quote_size')->nullable();
            $table->decimal('quote_qty', 10, 2)->default(0);
            $table->text('quote_parameters')->nullable();
            $table->decimal('quote_unit_price', 10, 2)->default(0);
            $table->decimal('quote_vat_exclude', 10, 2)->default(0);
            $table->decimal('quote_total_price', 10, 2)->default(0);
            $table->unsignedBigInteger('supplier_id')->nullable();
            $table->decimal('vat', 10, 2)->default(0);
            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('sales_quotation_id')
                ->references('id')
                ->on('sales_quotations')
                ->cascadeOnDelete();
            $table->foreign('waste_stream_id')
                ->references('id')
                ->on('waste_streams')
                ->cascadeOnDelete();
            $table->foreign('supplier_id')
                ->references('id')
                ->on('supplier')
                ->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_items');
    }
};
