<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserSenderDetails extends Model
{
    //
    use SoftDeletes;
    protected $table = "user_sender_details";
    
    public function senderDetails(){
        return $this->belongsTo(SenderDetails::class, "sender_details_id");
    }
}
