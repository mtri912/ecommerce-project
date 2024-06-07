<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public function product_images() {
        return $this->hasMany(ProductImage::class);
    }

    public function product_ratings() {
        return $this->hasMany(ProductRating::class)->where('status',1);
    }

    public function attributes() {
        return $this->hasMany(ProductsAttribute::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }
    public static function getAttributePrice($product_id,$size) {
        $attributePrice = ProductsAttribute::where(['product_id'=>$product_id,'size'=>$size])->first()->toArray();

        $final_price = $attributePrice['price'];

        return array('product_price'=>$attributePrice['price'],'final_price'=>$final_price);
    }

}
