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
        Schema::create('categorys', function (Blueprint $table) {
            $table->id();
            $table->string('name', 55); 
            $table->string('description'); 
            $table->string('image',1255)->nullable();
            $table->integer('is_requested')->default(0)->comment('0 => Created by Admin,  1 => Requested by restaurants');
            $table->integer('is_admin_approved')->default(0)->comment('1 => Admin Approved,  0 => Not Approved');
            $table->integer('created_by')->default(0)->comment('Will have Value if Requested by Restaurants');
            $table->string('description'); 
            $table->integer('status')->default(1)->comment('1 => enabled,  0 => disabled');
            $table->integer('order_index')->default(0);
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
