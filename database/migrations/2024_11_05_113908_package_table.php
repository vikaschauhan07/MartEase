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
        Schema::create('packages', function (Blueprint $table) {
            $table->id(); 
            $table->double('height');
            $table->double('width'); 
            $table->double('length');
            $table->string('type'); 
            $table->double('area'); 
            $table->double('shipping_fee');
            $table->integer('step')->default(1);
            $table->string('reference_number')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('sender_details_id')->nullable()->constrained('sender_details')->onDelete('set null');
            $table->foreignId('reciver_details_id')->nullable()->constrained('reciver_details')->onDelete('set null');
            $table->string('status')->default(0); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
