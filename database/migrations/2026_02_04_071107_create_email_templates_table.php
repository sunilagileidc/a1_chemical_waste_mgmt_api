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
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_name', 100)->comment('Name of the email template');
            $table->string('template_subject', 100)->comment('Subject of the email template');
            $table->text('template_body')->comment('Body of the email template');
            $table->string('template_signature', 4000)->nullable()->comment('Signature of the email template');
            $table->string('can_override', 1)->default('N')->comment('Status if the template can override');
            $table->integer('template_type_id')->comment('Template type ID');
            $table->integer('status')->default(1)->comment('Status if the record is active');
            $table->string('slug', 1000)->nullable()->comment('Uniquely generated slug for system email template');
            $table->string('is_standard', 1)->default('Y')->comment('Status if the template is standard ');
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
        Schema::dropIfExists('email_templates');
    }
};
