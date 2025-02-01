<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserChats extends Model
{
    use SoftDeletes;
    protected $table = "chats";

    public function messages(){
        return $this->hasMany(UserMessages::class, "chat_id");
    }

    public function fromUser(){
        return $this->belongsTo(User::class, "from_id");
    }
}
