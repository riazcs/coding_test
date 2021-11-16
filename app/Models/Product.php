<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'title', 'sku', 'description'
    ];

    public function variant(){
        return $this->hasMany(Variant::class,'product_id','id');
    }
    
    public function product_price_variant()
    {
        return $this->hasMany(ProductVariantPrice::class,'id','product_id');
        # code...
    }
}
