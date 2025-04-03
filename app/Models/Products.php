<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{
    protected $table = 'products';

    public function images()
    {
        return $this->hasMany(ProductImages::class, 'product_id');
    }

    public function category()
    {
        return $this->belongsTo(Categorys::class, 'category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function prices()
    {
        return $this->hasMany(ProductPrices::class, 'product_id');
    }
}
