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
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55);
            $table->string('email', 125)->unique();
            $table->string('phone_number', 20)->nullable();
            $table->string('password');
            $table->tinyInteger('is_email_verified')->default(0);
            $table->tinyInteger('is_phone_verified')->default(0);
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->tinyInteger('is_admin_approved')->default(0)->comment('1 => Admin Approved,  0 => Not Approved');
            $table->string('profile_image', 1255)->nullable();
            $table->tinyInteger('status')->default(1)->comment('0 => InActive,  1 => Active');
            $table->integer("step_completed")->default(1);
            $table->tinyInteger('device_type')->default('0')->comment('1 => Android,  0 => IOS');
            $table->string('socket_id')->nullable();
            $table->string("old_data")->nullable();
            $table->string("reason")->nullable();
            $table->string("time_zone")->nullable();
            $table->rememberToken();
            $table->softDeletes(); 
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
