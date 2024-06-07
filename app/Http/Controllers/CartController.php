<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CustomerAddress;
use App\Models\DiscountCoupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductsAttribute;
use App\Models\ShippingCharge;
use Carbon\Carbon;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Session\Store;

class CartController extends Controller
{
//    public function addToCart(Request $request) {
//        $product = Product::find($request->id);
//
//        if($product == null) {
//            return response()->json([
//                'status' => false,
//                'message' => 'Record not found'
//            ]);
//        }
//        if (Cart::count() > 0) {
//
//            $cartContent = Cart::content();
//            $productAlreadyExist = false;
//
//            foreach ($cartContent as $item) {
//                if($item->id == $product->id) {
//                    $productAlreadyExist = true;
//                    Cart::update($item->rowId, $item->qty + 1);
//                }
//            }
//
//            if ($productAlreadyExist == false) {
//                Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
//
//                $status = true;
//                $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
//                session()->flash('success',$message);
//            } else {
//                $status = false;
//                $message = $product->title.' already added in cart';
//            }
//        } else {
//            Cart::add($product->id, $product->title, 1, $product->price, ['productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '']);
//            $status = true;
//            $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
//            session()->flash('success',$message);
//        }
//
//
//        return response()->json([
//            'status' => $status,
//            'message' => $message
//        ]);
//    }

//    public function addToCart(Request $request) {
//        $product = Product::find($request->id);
//
//        if($product == null) {
//            return response()->json([
//                'status' => false,
//                'message' => 'Record not found'
//            ]);
//        }
//
//        // Lấy màu sắc từ sản phẩm
//        $color = $product->family_color; // Giả sử cột màu trong bảng sản phẩm là 'color'
//        $size = $request->size;
//
//        if (Cart::count() > 0) {
//            $cartContent = Cart::content();
//            $productAlreadyExist = false;
//
//            foreach ($cartContent as $item) {
//                if($item->id == $product->id && $item->family_color == $color && $item->options->size == $size) {
//                    $productAlreadyExist = true;
//                    Cart::update($item->rowId, $item->qty + 1);
//                }
//            }
//
//            if ($productAlreadyExist == false) {
//                Cart::add($product->id, $product->title, 1, $product->price, [
//                    'productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '',
//                    'color' => $color,
//                    'size' => $size
//                ]);
//
//                $status = true;
//                $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
//                session()->flash('success', $message);
//            } else {
//                $status = false;
//                $message = $product->title.' already added in cart';
//            }
//        } else {
//            Cart::add($product->id, $product->title, 1, $product->price, [
//                'productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '',
//                'color' => $color,
//                'size' => $size
//            ]);
//            $status = true;
//            $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
//            session()->flash('success', $message);
//        }
//
//        return response()->json([
//            'status' => $status,
//            'message' => $message
//        ]);
//    }

    public function addToCart(Request $request) {
        $product = Product::find($request->id);

        if ($product == null) {
            return response()->json([
                'status' => false,
                'message' => 'Record not found'
            ]);
        }

        // Lấy giá trị màu và kích thước từ sản phẩm
        $color = $product->family_color; // Giả sử cột màu trong bảng sản phẩm là 'color'
        $size = $request->size;

        // Lấy giá trị final_price dựa trên kích thước sản phẩm
        $attributePrice = Product::getAttributePrice($product->id, $size);
        $finalPrice = $attributePrice['final_price'];

        if (Cart::count() > 0) {
            $cartContent = Cart::content();
            $productAlreadyExist = false;

            foreach ($cartContent as $item) {
                if ($item->id == $product->id && $item->family_color == $color && $item->options->size == $size) {
                    $productAlreadyExist = true;
                    Cart::update($item->rowId, $item->qty + 1);
                }
            }

            if ($productAlreadyExist == false) {
                Cart::add($product->id, $product->title, 1, $finalPrice, [
                    'productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '',
                    'color' => $color,
                    'size' => $size
                ]);

                $status = true;
                $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
                session()->flash('success', $message);
            } else {
                $status = false;
                $message = $product->title.' already added in cart';
            }
        } else {
            Cart::add($product->id, $product->title, 1, $finalPrice, [
                'productImage' => (!empty($product->product_images)) ? $product->product_images->first() : '',
                'color' => $color,
                'size' => $size
            ]);
            $status = true;
            $message = '<strong>'.$product->title.'</strong> added in your cart successfully';
            session()->flash('success', $message);
        }

        return response()->json([
            'status' => $status,
            'message' => $message
        ]);
    }



    public function cart() {
        $cartContent = Cart::content();
//        echo "<pre>"; print_r($cartContent); die;
        $data['cartContent'] = $cartContent;
        return view('front.cart',$data);
    }

    public function updateCart(Request $request) {
        $rowId = $request->rowId;
        $qty = $request->qty;

        $itemInfo = Cart::get($rowId);

        $product = Product::find($itemInfo->id);

        // check qty available in stock

        if($product->track_qty == 'Yes') {

            if ($qty <= $product->qty ) {
                Cart::update($rowId, $qty);
                $message = 'Cart updated successfully';
                $status = true;
                session()->flash('success',$message);
            } else {
                $message = 'Request qty('.$qty.') not available in stock.';
                $status = false;
                session()->flash('error',$message);
            }
        } else {
            Cart::update($rowId, $qty);
            $message = 'Cart updated successfully';
            $status = true;
            session()->flash('success',$message);
        }

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function deleteItem(Request $request) {

        $itemInfo = Cart::get($request->rowId);

        if($itemInfo == null) {
            $errorMessage = 'Item not found in cart';
            session()->flash('error',$errorMessage);
            return response()->json([
                'status' => false,
                'message' => $errorMessage
            ]);
        }
        Cart::remove($request->rowId);

        $message = 'Item removed from cart successfully.';
        session()->flash('success',$message);
        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function checkout() {

        $discount = 0;
        //-- if cart is empty redirect to cart page
        if(Cart::count() == 0) {
            return redirect()->route('front.cart');
        }
        //-- if user is not logged in then redirect to login page
        if(Auth::check() == false) {
            if(!session()->has('url.intended')) {
                session(['url.intended' => url()->current()]);
            }
            return redirect()->route('account.login');
        }

        $customerAddress = CustomerAddress::where('user_id',Auth::user()->id)->first();


        session()->forget('url.intended');

        $countries = Country::orderBy('name','ASC')->get();

        $subTotal = Cart::subtotal(2,'.','');
        // Apply Discount Here
        if(session()->has('code')) {

            $code = session()->get('code');

            if($code->type == 'percent') {
                $discount = ($code->discount_amount/100)*$subTotal;
            } else {
                $discount = $code->discount_amount;
            }
        }


        // Calculate shipping here
        if($customerAddress != null) {
            $userCountry = $customerAddress->country_id;
            $shippingInfo = ShippingCharge::where('country_id', $userCountry)->first();

            $totalShippingCharge = 0;
            $grandTotal = 0;

            if($shippingInfo != null) {
                $totalShippingCharge = $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$totalShippingCharge;
            } else {
                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();
                $totalShippingCharge = $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$totalShippingCharge;
            }
        } else {
            $grandTotal = ($subTotal-$discount);
            $totalShippingCharge = 0;
        }

        return view('front.checkout',[
            'countries' => $countries,
            'customerAddress' => $customerAddress,
            'totalShippingCharge' => $totalShippingCharge,
            'discount' => $discount,
            'grandTotal' => $grandTotal
        ]);
    }

    public function processCheckout(Request $request) {
        // step 1 - Apply Validation
        $validator = Validator::make($request->all(),[
            'first_name' => 'required|min:5',
            'last_name' => 'required',
            'email' => 'required|email',
            'country' => 'required',
            'address' => 'required|min:30',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'mobile' => 'required',
        ]);

        if($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the errors',
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }

        // step 2 - save user address
        $user = Auth::user();
        CustomerAddress::updateOrCreate(
            ['user_id' => $user->id ],
            [
                'user_id' => $user->id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'mobile' => $request->mobile,
                'country_id' => $request->country,
                'address' => $request->address,
                'apartment' => $request->apartment,
                'city' => $request->city,
                'state' => $request->state,
                'zip' => $request->zip,
            ]
        );

        // step - 3 store data in orders table
        if($request->payment_method == 'cod') {

            $discountCodeId = NULL;
            $promoCode = '';
            $shipping = 0;
            $discount = 0;
            $subTotal = Cart::subtotal(2,'.','');
//            $grandTotal = $subTotal+$shipping;

            // Apply Discount Here
            if(session()->has('code')) {
                $code = session()->get('code');

                if($code->type == 'percent') {
                    $discount = ($code->discount_amount/100)*$subTotal;
                } else {
                    $discount = $code->discount_amount;
                }

                $discountCodeId = $code->id;
                $promoCode = $code->code;
            }

            // Calculate shipping
            $shippingInfo = ShippingCharge::where('country_id',$request->country)->first();

            $totalQty = 0;
            foreach (Cart::content() as $item) {
                $totalQty += $item->qty;
            }

            if($shippingInfo != null) {
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shipping;
            } else {
                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();
                $shipping = $totalQty*$shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shipping;
            }


            $order = new Order;
            $order->subtotal = $subTotal;
            $order->shipping = $shipping;
            $order->grand_total = $grandTotal;
            $order->discount = $discount;
            $order->coupon_code_id = $discountCodeId;
            $order->coupon_code = $promoCode;
            $order->payment_status = 'not paid';
            $order->status = 'pending';
            $order->user_id = $user->id;
            $order->first_name = $request->first_name;
            $order->last_name = $request->last_name;
            $order->email = $request->email;
            $order->mobile = $request->mobile;
            $order->address = $request->address;
            $order->apartment = $request->apartment;
            $order->state = $request->state;
            $order->city = $request->city;
            $order->zip = $request->zip;
            $order->notes = $request->order_notes;
            $order->country_id = $request->country;
            $order->save();

            // step - 4 store order items in order items table

            foreach (Cart::content() as $item) {
                $orderItem = new OrderItem;
                $orderItem->product_id = $item->id;
                $orderItem->order_id = $order->id;
                $orderItem->name = $item->name;
                $orderItem->size = $item->options->size;
                $orderItem->qty = $item->qty;
                $orderItem->price = $item->price;
                $orderItem->total = $item->price*$item->qty;
                $orderItem->save();

                // Update Product Stock
                $productData = Product::find($item->id);
                if($productData->track_qty == 'Yes') {
                    $currentQty = $productData->qty;
                    $updatedQty = $currentQty-$item->qty;
                    $productData->qty = $updatedQty;
                    $productData->save();
                }

                if($request->payment_method == 'cod') {
                    // Reduce Stock Script Start
                    $getProductstock = ProductsAttribute::productStock($item->id,$item->options->size);
                    $newStock = $getProductstock - $item->qty;
                    ProductsAttribute::where(['product_id'=>$item->id,'size'=>$item->options->size])->update(['stock'=>$newStock]);
                }
            }

            // Send Order Email
            orderEmail($order->id, 'customer');


            session()->flash('success','You have successfully placed your order.');

            Cart::destroy();

            session()->forget('code');

            return response()->json([
                'message' => 'Order saved successfully.',
                'orderId' =>$order->id,
                'status' => true,
            ]);



        } else {
            //
        }
    }

    public function thankyou($id) {
        return view('front.thanks',['id' => $id]);
    }

    public function getOrderSummery(Request $request) {

        $subTotal = Cart::subtotal(2,'.','');
        $discount = 0;
        $discountString = '';

        // Apply Discount Here
        if(session()->has('code')) {
            $code = session()->get('code');

            if($code->type == 'percent') {
                $discount = ($code->discount_amount/100)*$subTotal;
            } else {
                $discount = $code->discount_amount;
            }

            $discountString = ' <div class="mt-4" id="discount-response">
                            <strong>'.session()->get('code')->code.'</strong>
                            <a class="btn btn-sm btn-danger" id="remove-discount"><i class="fa fa-times"></i></a>
            </div>';
        }



        if ($request->country_id > 0) {

            $shippingInfo = ShippingCharge::where('country_id',$request->country_id)->first();

            if($shippingInfo != null) {

                $shippingCharge = $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shippingCharge;


                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => number_format($discount,2),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge,2),
                ]);
            } else {
                $shippingInfo = ShippingCharge::where('country_id','rest_of_world')->first();

                $shippingCharge = $shippingInfo->amount;
                $grandTotal = ($subTotal-$discount)+$shippingCharge;

                return response()->json([
                    'status' => true,
                    'grandTotal' => number_format($grandTotal,2),
                    'discount' => number_format($discount,2),
                    'discountString' => $discountString,
                    'shippingCharge' => number_format($shippingCharge,2),
                ]);
            }
        } else {
            return response()->json([
                'status' => true,
                'grandTotal' => number_format(($subTotal-$discount),2),
                'discount' => number_format($discount,2),
                'discountString' => $discountString,
                'shippingCharge' => number_format(0,2),
            ]);
        }
    }

    public function applyDiscount(Request $request) {


        $code = DiscountCoupon::where('code', $request->code)->first();



        $now = Carbon::now();
//        echo  $now->format('Y-m-d H:i:s');

        if ($code == null) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid discount coupon',
            ]);
        }

        // Check if coupon start date is valid or not


        if($code->starts_at != "") {
            $startDate = Carbon::createFromFormat('Y-m-d H:i:s',$code->starts_at);

            if($now->lt($startDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon',
                ]);
            }
        }

        if($code->expires_at != "") {
            $endDate = Carbon::createFromFormat('Y-m-d H:i:s',$code->expires_at);

            if($now->gt($endDate)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon',
                ]);
            }
        }

        // Max Uses Check
        if($code->max_uses > 0) {
            $couponUsed = Order::where('coupon_code_id', $code->id)->count();

            if($couponUsed >= $code->max_uses) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid discount coupon',
                ]);
            }
        }

        // Max Uses User Check
        if ($code->max_uses_user > 0) {
            $couponUsedByUser = Order::where(['coupon_code_id' => $code->id, 'user_id' => Auth::user()->id])->count();

            if($couponUsedByUser >= $code->max_uses_user) {
                return response()->json([
                    'status' => false,
                    'message' => 'You already used this coupon.',
                ]);
            }
        }

        $subTotal = Cart::subtotal(2,'.','');
        // Min amount condition check
        if($code->min_amount > 0) {
            if($subTotal < $code->min_amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Your min amount must be $'.$code->min_amount.'.',
                ]);
            }
        }



        session()->put('code', $code);

        return $this->getOrderSummery($request);
    }

    public function removeCoupon(Request $request) {
        session()->forget('code');
        return $this->getOrderSummery($request);
    }


}
