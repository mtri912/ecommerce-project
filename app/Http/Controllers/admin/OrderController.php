<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderFeedback;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request) {

        $orders = Order::latest('orders.created_at')->select('orders.*','users.name','users.email');
        $orders = $orders->leftJoin('users','users.id','orders.user_id');
        if($request->get('keyword') != "") {
            $orders = $orders->where('users.name','like','%'.$request->keyword.'%');
            $orders = $orders->orWhere('users.email','like','%'.$request->keyword.'%');
            $orders = $orders->orWhere('orders.id','like','%'.$request->keyword.'%');
        }

        $orders = $orders->paginate(10);

        return view('admin.orders.list',[
            'orders' => $orders
        ]);
    }

    public function detail($orderId) {
        $order = Order::select('orders.*','countries.name as countryName')
                    ->where('orders.id',$orderId)
                    ->leftJoin('countries','countries.id','orders.country_id')
                    ->first();

        $orderItems = OrderItem::where('order_id',$orderId)->get();

        return view('admin.orders.detail',[
            'order' => $order,
            'orderItems' => $orderItems
        ]);
    }

    public function changeOrderStatus(Request $request, $orderId) {
        $order = Order::find($orderId);
        $order->status = $request->status;
        $order->shipped_date = $request->shipped_date;
        $order->save();

        $message = 'Order status updated successfully';

        session()->flash('success',$message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function sendInvoiceEmail(Request $request, $orderId) {
        orderEmail($orderId, $request->userType);

        $message = 'Order email sent successfully';

        session()->flash('success',$message);

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }

    public function cancelOrder(Request $request) {
        $order = Order::find($request->order_id);

        if ($order) {
            // Kiểm tra trạng thái của đơn hàng
            if ($order->status != 'pending') {
                return response()->json(['status' => false, 'message' => 'Only pending orders can be cancelled']);
            }

            // Lưu feedback
            $feedback = new OrderFeedback();
            $feedback->order_id = $order->id;
            $feedback->feedback = $request->feedback;
            $feedback->save();

            // Cập nhật trạng thái đơn hàng
            $order->status = 'cancelled';
            $order->save();

            return response()->json(['status' => true, 'message' => 'Order cancelled successfully']);
        }

        return response()->json(['status' => false, 'message' => 'Order not found']);
    }
}
