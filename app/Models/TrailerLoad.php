<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrailerLoad extends Model
{
    protected $table = "trailer_load";
   
    public function trailer(){
        return $this->belongsTo(Trailers::class, "trailer_id");
    }

    public function package(){
        return $this->belongsTo(Packages::class, "package_id");
    }

}
