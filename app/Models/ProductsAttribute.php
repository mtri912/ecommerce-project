<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductsAttribute extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsTo(Product::class);
    }

    public static function productStock($product_id,$size) {
        $productStock = ProductsAttribute::select('stock')->where(['product_id'=>$product_id,'size'=>$size])->first();
        return $productStock->stock;
    }
}
