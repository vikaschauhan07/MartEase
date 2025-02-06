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
            $table->id();
            $table->string('name',55)->nullable();
            $table->string('email',150)->nullable();
            $table->string('phone_code',10)->nullable();
            $table->string('phone_number',20)->nullable();
            $table->string('profile_image')->nullable();
            $table->tinyInteger('is_email_verified')->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->tinyInteger("device_type")->default(1)->comment("1 : for Ios, 2: for andriod");
            $table->tinyInteger('terms_conditions')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->tinyInteger('online_status')->default(0);
            $table->tinyInteger('is_password_set')->default(1);
            $table->string("old_data")->nullable();
            $table->string("time_zone")->nullable();
            $table->string("unique_apple_id")->nullable();
            $table->string("socket_id")->nullable();
            $table->softDeletes();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
