<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReciverDetails extends Model
{
    //
    use SoftDeletes;
    protected $table = "reciver_details";
}
