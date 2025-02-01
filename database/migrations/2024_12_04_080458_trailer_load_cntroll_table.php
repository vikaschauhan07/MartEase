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
        Schema::create('trailer_load', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trailer_id')->nullable()->constrained('trailers')->onDelete('set null');
            $table->foreignId('package_id')->nullable()->constrained('packages')->onDelete('set null');
            $table->tinyInteger("is_erased")->default(0);
            $table->tinyInteger("status")->default(1);
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
