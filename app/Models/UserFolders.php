<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserFolders extends Model
{
    use SoftDeletes;
    protected $table = "folders";

    public function childFolders(){
        return $this->hasMany(UserFolders::class, "parent_folder_id");
    }

    public function files(){
        return $this->hasMany(UserDocumnents::class, "folder_id");
    }
    
}
