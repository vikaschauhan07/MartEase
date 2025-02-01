<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Likes extends Model
{
    protected $table = "likes";

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }
}
