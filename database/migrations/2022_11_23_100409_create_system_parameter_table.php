<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSystemParameterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('system_parameter', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('Primary Key Column');
            $table->string('parameter_name', 30)->unique()->comment('Unique name of the parameter');
            $table->string('parameter_value', 1000)->comment('Value of the parameter');
            $table->string('description', 4000)->comment('Description of the parameter');
            $table->bigInteger('is_file_upload')->comment('URL of the attachment')->default(0);
            $table->string('status', 1)->default(1)->comment('Is the record active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for system parameter');
            $table->integer('created_by')->nullable()->comment('Who created the record');
            $table->integer('updated_by')->nullable()->comment('Who last updated the record');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('system_parameter');
    }
}
