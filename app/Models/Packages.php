<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Packages extends Model
{
    protected $table = "packages";

    public function senderDetails(){
        return $this->belongsTo(SenderDetails::class, "sender_details_id");
    }

    public function reciverDetails(){
        return $this->belongsTo(ReciverDetails::class, "reciver_details_id");
    }

    public function packageImages(){
        return $this->hasMany(PackageImages::class, "package_id");
    }

    public function packaageLoadedTrailer(){
        return $this->hasOne(TrailerLoad::class, "package_id");
    }
}
