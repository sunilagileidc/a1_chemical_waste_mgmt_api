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
        Schema::create('supplier_sales_data', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('supplier', 255)->nullable()->comment('Supplier name');
            $table->date('invoice_date')->nullable()->comment('Invoice date');
            $table->unsignedBigInteger('invoice_no')->nullable()->comment('Invoice number');
            $table->string('order_ref', 100)->nullable()->comment('Order reference number');
            $table->string('pip_code', 100)->nullable()->comment('Product PIP code');
            $table->string('account_no', 150)->nullable()->comment('Customer account number');
            $table->string('customer_name', 255)->nullable()->comment('Customer full name');
            $table->text('address1')->nullable()->comment('First Address');
            $table->text('address2')->nullable()->comment('Second Address');
            $table->text('address3')->nullable()->comment('Third Address');
            $table->string('postcode', 100)->nullable()->comment('Postcode');
            $table->integer('quantity')->default(0)->comment('Quantity sold');
            $table->integer('pack')->default(0)->comment('Pack size');
            $table->string('product_description', 255)->nullable()->comment('Description for the product');
            $table->string('batch_no', 100)->nullable()->comment('Batch number');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for system states');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplier_sales_data');
    }
};
