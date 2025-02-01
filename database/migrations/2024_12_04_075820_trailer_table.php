<?php

use App\Models\Trailers;
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
        Schema::create('trailers', function (Blueprint $table) {
            $table->id();
            $table->string("trailer_number")->nullable();
            $table->tinyInteger("is_locked")->default(0);
            $table->tinyInteger("status")->default(1);
            $table->softDeletes(); 
            $table->timestamps();
        });

        for($i = 1; $i <= 20; $i++){
            $trailer = new Trailers();
            $trailer->trailer_number = $i;
            $trailer->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
