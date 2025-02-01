<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SenderDetails extends Model
{
    use SoftDeletes;
    protected $table = "sender_details";

}
