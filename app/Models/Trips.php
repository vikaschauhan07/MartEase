<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trips extends Model
{
    use SoftDeletes;
    protected $table = "trips";
}
