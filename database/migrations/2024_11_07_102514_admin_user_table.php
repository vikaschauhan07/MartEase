<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('name',55);
            $table->string('email',125)->unique();
            $table->string('phone_code', 10)->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->string('password');
            $table->integer('notification')->default(1)->comment('1 => ON,  0 => OFF');
            $table->integer('status')->default('1')->comment('1 => Active,  0 => Inactive');
            $table->tinyInteger('language')->default(0)->comment('0 => English,  1 => Albelian(Al)');
            $table->string('profile_pic',255)->nullable();
            $table->string('otp',6)->nullable();
            $table->rememberToken();
            $table->string('socket_id', 255)->nullable();
            $table->timestamps();
        });
        DB::table('admin')->insert([
            'name' => 'HUNKR',
            'email' => 'hunkr@yopmail.com',
            'password' => Hash::make('Test@123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
