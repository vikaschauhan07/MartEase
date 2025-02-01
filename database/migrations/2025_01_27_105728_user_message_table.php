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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->onDelete('cascade');
            $table->integer('from_id');
            $table->integer('to_id');
            $table->text('message')->nullable();
            $table->integer('type')->default(1);
            $table->integer('model_id')->nullable();
            $table->integer('message_id')->nullable()->comment("Got value when replied");
            $table->integer('is_forword')->default(0)->comment("0 ofor not 1 for forwared");
            $table->tinyInteger('is_read')->default(0)->comment('0-> NOT READ,1-> READ');
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
