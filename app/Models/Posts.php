<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Posts extends Model
{
    use SoftDeletes;
    protected $table = "posts";

    public function user(){
        return $this->belongsTo(User::class, "user_id");
    }

    public function postFiles(){
        return $this->hasMany(PostFiles::class, "post_id");
    }

    public function likes(){
        return $this->hasMany(Likes::class, "model_id")->where("model_type", Posts::class);
    }

    public function comments(){
        return $this->hasMany(Comments::class, "post_id");
    }
    
}
