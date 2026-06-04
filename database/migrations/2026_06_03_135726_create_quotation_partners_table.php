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
        Schema::create('quotation_partners', function (Blueprint $table) {

            $table->bigIncrements('id')->comment('Primary Key');
            $table->unsignedBigInteger('sales_quotation_id')->nullable();
            $table->enum('partner_type', ['supplier', 'haulier'])->nullable();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->date('quotation_date')->nullable();
            $table->decimal('transport_cost', 10, 2)->default(0);
            $table->decimal('document_cost', 10, 2)->default(0);
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);
            $table->string('manual_figures')->nullable();
            $table->decimal('fuel_charge', 10, 2)->default(0);
            $table->decimal('demurrage_charge', 10, 2)->default(0);
            $table->string('load_type')->nullable();
            $table->string('load_other')->nullable();
            $table->string('number_pallets')->nullable();
            $table->text('haulier_notes')->nullable();
            $table->boolean('quote_finalised')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->boolean('active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->foreign('sales_quotation_id')
                ->references('id')
                ->on('sales_quotations')
                ->cascadeOnDelete();
            $table->foreign('partner_id')
                ->references('id')
                ->on('supplier') // Assuming both suppliers and hauliers are stored in the 'supplier' table
                ->nullOnDelete();    

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_partners');
    }
};
