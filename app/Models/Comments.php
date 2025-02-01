<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $table = "comments";

    public function likes(){
        return $this->hasMany(Likes::class, "model_id")->where("model_type", Comments::class);
    }

    public function reply(){
        return $this->hasMany(Comments::class, "comment_id");
    }

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

}
