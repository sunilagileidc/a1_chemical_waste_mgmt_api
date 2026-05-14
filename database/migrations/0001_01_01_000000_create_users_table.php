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
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id')->comment('primary key');
            $table->string('name')->comment('User name')->nullable();
            $table->string('lastname', 100)->comment('User last name')->nullable();
            $table->string('email')->unique()->comment('User email and its unique')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->comment('User password')->nullable();
            $table->string('token_id', 1000)->comment('token id of user')->nullable();
            $table->integer('role_id')->comment('User role');
            $table->string('salutation', 10)->comment('Salutation')->nullable();
            $table->string('gender', 10)->comment('Gender of the user')->nullable();
            $table->date('dob')->comment('Date of Birth')->nullable();
            $table->string('address', 250)->comment('User address')->nullable();
            $table->string('postcode', 100)->comment('Area Postcode')->nullable();
            $table->longText('description', 500)->comment('Description if any for the user')->nullable();
            $table->string('image_url', 200)->comment('Image Url')->nullable();
            $table->integer('country')->comment('Name of the Country')->nullable();
            $table->integer('state')->comment('Name of the State')->nullable();
            $table->integer('city')->comment('Name of the City')->nullable();
            $table->string('mobile', 25)->comment('Mobile number')->nullable();
            $table->integer('mobile_code')->comment('Mobile code')->nullable();
            $table->string('otp', 100)->comment('Generated otp for recover password')->nullable();
            $table->dateTime('otp_valid_until')->comment('Generated otp expire date and time')->nullable();
            $table->integer('is_otp_validated')->default(1)->comment('Is the OTP Validated?')->nullable();
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->comment('Uniquely generated slug')->nullable();
            $table->integer('created_by')->comment('Who created the record')->nullable();
            $table->integer('updated_by')->comment('Who last updated the record')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
