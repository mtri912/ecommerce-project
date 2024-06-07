<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductsAttribute;
use Illuminate\Http\Request;

class ProductAttributeController extends Controller
{
//    public function index()
//    {
//        $products = Product::with(['attributes'])
//            ->get()
//            ->map(function ($product) {
//                return [
//                    'id' => $product->id,
//                    'name' => $product->title,
//                    'color' => $product->product_color,
//                    'attributes' => $product->attributes->map(function ($attribute) {
//                        return [
//                            'size' => $attribute->size,
//                            'price' => $attribute->price,
//                            'stock' => $attribute->stock,
//                            'sku' => $attribute->sku,
//                            'status' => $attribute->status
//                        ];
//                    })
//                ];
//            });
//
//
//        return view('admin.products.inventory', compact('products'));
//    }

    public function index()
    {
        // Apply pagination directly on the query result
        $products = Product::latest('id')->with('attributes')->paginate(0); // You can change the number per page as needed
        // Now modify each product and its attributes after fetching
        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->title, // Assuming the column name is 'title'
                'color' => $product->product_color, // Ensure column name matches
                'attributes' => $product->attributes->map(function ($attribute) {
                    return [
                        'size' => $attribute->size,
                        'price' => $attribute->price,
                        'stock' => $attribute->stock,
                        'sku' => $attribute->sku,
                        'status' => $attribute->status
                    ];
                })
            ];
        });

        return view('admin.products.inventory', compact('products'));
    }

}
