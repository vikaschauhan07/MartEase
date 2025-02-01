<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Trailers extends Model
{
    protected $table = "trailers";

    public function trailerLoad(){
        return $this->hasMany(TrailerLoad::class, "trailer_id")->where("is_erased", 0);
    }
}
