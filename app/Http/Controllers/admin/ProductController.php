<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductRating;
use App\Models\ProductsAttribute;
use App\Models\SubCategory;
use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ProductController extends Controller
{
    public function index(Request $request) {
        $products = Product::latest('id')->with('product_images','attributes','category','subCategory','brand');
//        dd($products);
        if($request->get('keyword') != "") {
            $products = $products->where('title','like','%'.$request->keyword.'%');
        }

        $data['products'] = $products->paginate();
        return view('admin.products.list',$data);
    }
    public function inventory($id,Request $request) {
        $products = Product::latest('id')->with('product_images','attributes');
//        dd($products);
        if($request->get('keyword') != "") {
            $products = $products->where('title','like','%'.$request->keyword.'%');
        }

        $data['products'] = $products->paginate();
        return view('admin.products.inventory',$data);
    }
    public function create() {
        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        return view('admin.products.create',$data);
    }
    public function store(Request $request) {
//        dd($request->image_array);
//        exit();
        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products',
            'product_color' => 'required',
            'family_color' => 'required',
//            'price' => 'required|numeric',
//            'sku' => 'required|unique:products',
//            'track_qty' => 'required|in:Yes,No',
            'category' => 'required',
            'is_featured' => 'required|in:Yes,No',
        ];

//        if(!empty($request->track_qty && $request->track_qty == "Yes")) {
//            $rules['qty'] = 'required|numeric';
//        }

        $validator = Validator::make($request->all(),$rules);

        if ($request->isMethod('post')) {
            $data = $request->all();
//        echo "<pre>"; print_r($data); die;
        }


        if($validator->passes()) {
            $product = new Product;
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->product_color = $request->product_color;
            $product->family_color = $request->family_color;
            $product->product_price = $request->product_price;
            $product->compare_price = $request->compare_price;
//            $product->sku = $request->sku;
//            $product->barcode = $request->barcode;
//            $product->track_qty = $request->track_qty;
//            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();

            // Save Gallery Pics
            if(!empty($request->image_array)) {
                foreach ($request->image_array as $temp_image_id) {

                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.',$tempImageInfo->name);
                    $ext = last($extArray); // like ipg,gif,png etc

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id.'-'.$productImage->id.'-'.time().'.'.$ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    // Generate Product Thumbnails

                    // Large Image
                    $sourcePath = public_path().'/temp/'.$tempImageInfo->name;
                    $destPath = public_path().'/uploads/product/large/'.$imageName;
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($sourcePath);
                    $image->resize(1400,null,function ($contraint) {
                        $contraint->aspectRatio();
                    });
                    $image->save($destPath);

                    // Small Image
                    $destPath = public_path().'/uploads/product/small/'.$imageName;
                    $manager = new ImageManager(new Driver());
                    $image = $manager->read($sourcePath);
                    $image->resize(300,300);
                    $image->save($destPath);
                }
            }

            foreach ($data['sku'] as $key => $value) {
                if (!empty($value)) {
                    // SKU already exists check
                    $countSKU = ProductsAttribute::where('sku',$value)->count();
                    if ($countSKU > 0) {
                        $message = "SKU already exists. Please add another SKU";
                        session()->flash('error',$message);
                        return redirect()->back()->with('success',$message);
                    }
                    // Size already exists check
                    $countSize = ProductsAttribute::where(['product_id'=>$product->id,'size'=>$data['size'][$key]])->count();
                    if ($countSize > 0) {
                        $message= "Size already exists. Please add another Size";
                        return redirect()->back()->with('success',$message);
                    }

                    $attribute = new ProductsAttribute();
                    $attribute->product_id = $product->id;
                    $attribute->sku = $value;
                    $attribute->size = $data['size'][$key];
                    $attribute->price = $data['price'][$key];
                    $attribute->stock = $data['stock'][$key];
                    $attribute->status = 1;
                    $attribute->save();
                }
            }


            $request->session()->flash('success','Product added successfully');
            return response()->json([
                'status' => true,
                'message' => "Product added successfully"
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function edit($id, Request $request) {
        $product = Product::find($id);

        if(empty($product)) {

            return redirect()->route('products.index')->with('error','Product not found');
        }

        // Fetch Product Image
        $productImages = ProductImage::where('product_id',$product->id)->get();

        $subCategories = SubCategory::where('category_id',$product->category_id)->get();

        $relatedProducts = [];
        // fetch related products
        if($product->related_products != '') {
            $productArray = explode(',',$product->related_products);
            $relatedProducts = Product::whereIn('id',$productArray)->with('product_images')->get();
        }

        $data = [];
        $categories = Category::orderBy('name','ASC')->get();
        $brands = Brand::orderBy('name','ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['product'] = $product;
        $data['subCategories'] = $subCategories;
        $data['productImages'] = $productImages;
        $data['relatedProducts'] = $relatedProducts;
        return view('admin.products.edit',$data);
    }
    public function update($id, Request $request){
        $product = Product::find($id);

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,'.$product->id.',id',
//            'price' => 'required|numeric',
            'product_color' => 'required',
            'family_color' => 'required',
//            'sku' => 'required|unique:products,sku,'.$product->id.',id',
//            'track_qty' => 'required|in:Yes,No',
            'category' => 'required',
            'is_featured' => 'required|in:Yes,No',
        ];

//        if(!empty($request->track_qty && $request->track_qty == "Yes")) {
//            $rules['qty'] = 'required|numeric';
//        }

            $data = $request->all();
//        echo "<pre>"; print_r($data); die;


        $validator = Validator::make($request->all(),$rules);

        if($validator->passes()) {

            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->product_color = $request->product_color;
            $product->family_color = $request->family_color;
            $product->product_price = $request->product_price;
            $product->compare_price = $request->compare_price;
//            $product->sku = $request->sku;
//            $product->barcode = $request->barcode;
//            $product->track_qty = $request->track_qty;
//            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->short_description = $request->short_description;
            $product->shipping_returns = $request->shipping_returns;
            $product->related_products = (!empty($request->related_products)) ? implode(',',$request->related_products) : '';
            $product->save();


            foreach ($data['sku'] as $key => $value) {
                if (!empty($value)) {
                    // SKU already exists check
                    $countSKU = ProductsAttribute::where('sku',$value)->count();
                    if ($countSKU > 0) {
                        $message = "SKU already exists. Please add another SKU";
                        session()->flash('error',$message);
                        return response()->json([
                            'status' => false,
                            'message' => $message
                        ]);
                    }
                    // Size already exists check
                    $countSize = ProductsAttribute::where(['product_id'=>$product->id,'size'=>$data['size'][$key]])->count();
                    if ($countSize > 0) {
                        $message= "Size already exists. Please add another Size";
                        session()->flash('error',$message);
                        return response()->json([
                            'status' => false,
                            'message' => $message
                        ]);
                    }

                    $attribute = new ProductsAttribute();
                    $attribute->product_id = $product->id;
                    $attribute->sku = $value;
                    $attribute->size = $data['size'][$key];
                    $attribute->price = $data['price'][$key];
                    $attribute->stock = $data['stock'][$key];
                    $attribute->status = 1;
                    $attribute->save();
                }
            }

            foreach ($data['attributeId'] as $akey => $attribute) {
                if (!empty($attribute)) {
                    ProductsAttribute::where(['id'=>$data['attributeId'][$akey]])->update(['price'=>$data['price'][$akey],'stock'=>$data['stock'][$akey]]);
                }
            }


            $request->session()->flash('success','Product updated successfully');
            return response()->json([
                'status' => true,
                'message' => "Product updated successfully"
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }
    public function destroy($id, Request $request) {
        $product = Product::find($id);

        if(empty($product)) {
            $request->session()->flash('error','Product not found');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }
        $productImages = ProductImage::where('product_id',$id)->get();

        if(!empty($productImages)) {
            foreach ($productImages as $productImage) {
                File::delete(public_path('uploads/product/large/'.$productImage->image));
                File::delete(public_path('uploads/product/small/'.$productImage->image));
            }
            ProductImage::where('product_id',$id)->delete();
        }
        $product->delete();

        $request->session()->flash('success','Product deleted successfully');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully'
        ]);

    }
    public function getProducts(Request $request){
        $tempProduct = [];
        if($request->term != "") {
            $products = Product::where('title','like','%'.$request->term.'%')->get();
            if ($products != null) {
                foreach ($products as $product) {
                    $tempProduct[] = array('id' => $product->id, 'text' => $product->title);
                }
            }
        }
        return response()->json([
            'tags' => $tempProduct,
            'status' => true
        ]);
    }

    public function productRatings(Request $request) {
        $ratings = ProductRating::select('product_ratings.*','products.title as productTitle')->orderBy('product_ratings.created_at','DESC');
        $ratings = $ratings->leftJoin('products','products.id','product_ratings.product_id');


        if ($request->get('keyword') != "") {
            $ratings = $ratings->orWhere('products.title','like','%'.$request->keyword.'%');
            $ratings = $ratings->orWhere('product_ratings.username','like','%'.$request->keyword.'%');
        }

        $ratings = $ratings->paginate(10);

        return view('admin.products.ratings',[
            'ratings' => $ratings
        ]);
    }

    public function changeRatingStatus(Request $request) {

        $productRating = ProductRating::find($request->id);
        $productRating->status = $request->status;
        $productRating->save();

        session()->flash('success','Status changed successfully.');

        return response()->json([
           'status' => true,
        ]);
    }

    public function changeAttributeStatus(Request $request) {

        $productAttribute = ProductsAttribute::find($request->id);
        $productAttribute->status = $request->status;
        $productAttribute->save();

        session()->flash('success','Status changed successfully.');

        return response()->json([
            'status' => true,
        ]);
    }


    public function deleteAttribute($id) {
        $att = ProductsAttribute::find($id);
        $att->delete();

        session()->flash('success','Attribute deleted successfully.');

        return response()->json([
            'status' => true,
        ]);
    }
}


