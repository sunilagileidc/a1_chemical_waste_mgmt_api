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

        Schema::create('customer', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->string('company_name', 255);
            $table->text('company_address')->nullable();
            $table->string('company_postcode', 20)->nullable();
            $table->string('company_telephone', 20)->nullable();
            $table->string('company_email', 150)->nullable();

            $table->boolean('active')->default(1);

            $table->string('hwr_code', 100)->nullable();
            $table->date('hwr_expiry_date')->nullable();

            $table->string('sic_code', 100)->nullable();
            $table->text('sic_desc')->nullable();

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('customer_individual', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->unsignedBigInteger('customer_id');

            $table->string('name', 150);
            $table->string('telephone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('position', 100)->nullable();

            $table->boolean('active')->default(1);

            $table->foreign('customer_id')
                ->references('id')
                ->on('customer')
                ->onDelete('cascade');

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('supplier', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->string('supplier_name', 255);
            $table->text('supplier_address')->nullable();
            $table->string('supplier_postcode', 20)->nullable();
            $table->string('supplier_telephone', 20)->nullable();
            $table->string('supplier_email', 150)->nullable();

            $table->string('supplier_license', 255)->nullable();

            $table->boolean('active')->default(1);

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('supplier_individual', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->unsignedBigInteger('supplier_id');

            $table->string('name', 150);
            $table->string('telephone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('position', 100)->nullable();

            $table->boolean('active')->default(1);

            $table->foreign('supplier_id')
                ->references('id')
                ->on('supplier')
                ->onDelete('cascade');

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('haulier', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->string('haulier_name', 255);
            $table->text('haulier_address')->nullable();
            $table->string('haulier_postcode', 20)->nullable();
            $table->string('haulier_telephone', 20)->nullable();
            $table->string('haulier_email', 150)->nullable();

            $table->string('haulier_license', 255)->nullable();

            $table->boolean('active')->default(1);

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('haulier_individual', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->unsignedBigInteger('haulier_id');

            $table->string('name', 150);
            $table->string('telephone', 20)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('position', 100)->nullable();

            $table->boolean('active')->default(1);

            $table->foreign('haulier_id')
                ->references('id')
                ->on('haulier')
                ->onDelete('cascade');

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('customer_notes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->string('type', 50)->nullable();

            $table->unsignedBigInteger('customer_id');

            $table->text('note')->nullable();

            $table->dateTime('note_date_time')->nullable();

            $table->foreign('customer_id')
                ->references('id')
                ->on('customer')
                ->onDelete('cascade');

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('supplier_notes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->unsignedBigInteger('supplier_id');

            $table->text('note')->nullable();

            $table->dateTime('note_date_time')->nullable();

            $table->foreign('supplier_id')
                ->references('id')
                ->on('supplier')
                ->onDelete('cascade');

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('haulier_notes', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->unsignedBigInteger('haulier_id');

            $table->text('note')->nullable();

            $table->dateTime('note_date_time')->nullable();

            $table->foreign('haulier_id')
                ->references('id')
                ->on('haulier')
                ->onDelete('cascade');

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });

        Schema::create('file_uploads', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('Primary Key');

            $table->string('quotation_number', 100)->nullable();
            $table->string('file_loc', 500)->nullable();
            $table->string('file_name', 255)->nullable();

            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who updated the record');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_uploads');

        Schema::dropIfExists('haulier_notes');
        Schema::dropIfExists('supplier_notes');
        Schema::dropIfExists('customer_notes');

        Schema::dropIfExists('haulier_individual');
        Schema::dropIfExists('haulier');

        Schema::dropIfExists('supplier_individual');
        Schema::dropIfExists('supplier');

        Schema::dropIfExists('customer_individual');
        Schema::dropIfExists('customer');
    }
};
