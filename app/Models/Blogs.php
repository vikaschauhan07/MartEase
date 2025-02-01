<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blogs extends Model
{
    use SoftDeletes;
    protected $table  = "blogs";

    public function blogFiles(){
        return $this->hasMany(BlogFiles::class, "blog_id");
    }
}
