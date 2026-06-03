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
        Schema::create('waste_streams', function (Blueprint $table) {

            $table->bigIncrements('id')->comment('Primary Key');
            $table->string('waste_code')->nullable();
            $table->text('waste_description')->nullable();
            $table->enum('is_hazard', ['Y', 'N'])
                ->default('N');
            $table->text('waste_components')->nullable();
            $table->string('waste_ewc')->nullable();
            $table->string('waste_color')->nullable();
            $table->string('waste_physical_form')->nullable();
            $table->string('waste_haz_code')->nullable();
            $table->text('waste_risk_pharse')->nullable();
            $table->string('waste_un_no')->nullable();
            $table->string('waste_pkg_grp')->nullable();
            $table->string('waste_un_cls')->nullable();
            $table->string('waste_ship_name')->nullable();
            $table->text('waste_ass_raj')->nullable();
            $table->string('waste_rd_color')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_streams');
    }
};
