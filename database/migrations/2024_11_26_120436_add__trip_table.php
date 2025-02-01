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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->integer("trailer_number")->nullable();
            $table->double("trailer_length")->nullable();
            $table->double("trailer_breadth")->nullable();
            $table->double("trailer_height")->nullable();
            $table->double("trailer_weight")->nullable();
            $table->string("from_city")->nullable();
            $table->string("dropoff_location")->nullable();
            $table->string("pickup_location")->nullable();
            $table->string("to_city")->nullable();
            $table->double("delivery_price")->nullable();
            $table->date("pickup_date")->nullable();
            $table->integer("pickup_window")->default(1);
            $table->integer("dropoff_window")->default(1);
            $table->double("distance")->nullable();
            $table->integer("status")->default(1);
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
