@extends('front.layouts.app')

@section('content')
    <section class="section-5 pt-3 pb-3 mb-3 bg-white">
        <div class="container">
            <div class="light-font">
                <ol class="breadcrumb primary-color mb-0">
                    <li class="breadcrumb-item"><a class="white-text" href="#">My Account</a></li>
                    <li class="breadcrumb-item">Settings</li>
                </ol>
            </div>
        </div>
    </section>

    <section class=" section-11 ">
        <div class="container  mt-5">
            <div class="row">
                <div class="col-md-3">
                    @include('front.account.common.sidebar')
                </div>
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h2  id="order-{{ $order->id }}"  class="h5 mb-0 pt-2 pb-2">Order: {{ $order->id }}</h2>

                        </div>

                        <div class="card-body pb-0">
                            <!-- Info -->
                            <div class="card card-sm">
                                <div class="card-body bg-light mb-3">
                                    <div class="row">
                                        <div class="col-6 col-lg-2">
                                            <!-- Heading -->
                                            <h6 class="heading-xxxs text-muted">Order No:</h6>
                                            <!-- Text -->
                                            <p class="mb-lg-0 fs-sm fw-bold">
                                                {{ $order->id }}
                                            </p>
                                        </div>
                                        <div class="col-6 col-lg-2">
                                            <!-- Heading -->
                                            <h6 class="heading-xxxs text-muted">Shipped date:</h6>
                                            <!-- Text -->
                                            <p class="mb-lg-0 fs-sm fw-bold">
                                                <time datetime="2019-10-01">
                                                    @if(!empty($order->shipped_date))
                                                        {{ \Carbon\Carbon::parse($order->shipped_date)->format('d M, Y') }}
                                                    @else
                                                        n/a
                                                    @endif
                                                </time>
                                            </p>
                                        </div>
                                        <div class="col-6 col-lg-2">
                                            <!-- Heading -->
                                            <h6 class="heading-xxxs text-muted">Status:</h6>
                                            <!-- Text -->
                                            <p class="mb-0 fs-sm fw-bold">
                                                @if($order->status == 'pending')
                                                    <span class="badge bg-danger">Pending</span>
                                                @elseif($order->status == 'shipped')
                                                    <span class="badge bg-info">Shipped</span>
                                                @elseif($order->status == 'delivered')
                                                    <span class="badge bg-success">Delivered</span>
                                                @else
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @endif
                                            </p>
                                        </div>
                                        <div class="col-6 col-lg-2">
                                            <!-- Heading -->
                                            <h6 class="heading-xxxs text-muted">Order Amount:</h6>
                                            <!-- Text -->
                                            <p class="mb-0 fs-sm fw-bold">
                                                 {{ number_format($order->grand_total,2) }}
                                            </p>
                                        </div>
{{--                                        <div class="col-6 col-lg-3">--}}
{{--                                            <button id="cancelOrderButton" class="btn btn-danger">Cancel Order</button>--}}
{{--                                        </div>--}}
                                        <!-- Button Cancel Order -->
                                        <div class="col-6 col-lg-3">
                                            <button id="cancelOrderButton" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#cancelOrderModal">Cancel Order</button>
                                        </div>
                                        <!-- Modal Cancel Order -->
                                        <div class="modal fade" id="cancelOrderModal" tabindex="-1" aria-labelledby="cancelOrderModalLabel" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="cancelOrderModalLabel">Cancel Order</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <form id="cancelOrderForm">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p>Are you sure you want to cancel this order?</p>
                                                            <div class="mb-3">
                                                                <label for="feedback" class="form-label">Feedback</label>
                                                                <textarea class="form-control" id="feedback" name="feedback" rows="3" required></textarea>
                                                            </div>
                                                            <input type="hidden" id="orderId" name="order_id" value="{{ $order->id }}">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-danger">Cancel Order</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer p-3">

                            <!-- Heading -->
                            <h6 class="mb-7 h5 mt-4">Order Items ({{ $orderItemsCount }})</h6>


                            <!-- Divider -->
                            <hr class="my-3">

                            <!-- List group -->
                            <ul>
                                @foreach($orderItems as $item)
                                    <li class="list-group-item">
                                        <div class="row align-items-center">
                                            <div class="col-4 col-md-3 col-xl-2">
                                                <!-- Image -->
{{--                                                <a href="product.html"><img src="images/product-1.jpg" alt="..." class="img-fluid"></a>--}}
                                                @php
                                                    $productImage = getProductImage($item->product_id);
                                                @endphp
                                                @if(!empty($productImage->image))
                                                    <img class="img-fluid" src="{{ asset('uploads/product/small/'. $productImage->image) }}" />
                                                @else
                                                    <img src="{{ asset('admin-ascsets/img/default-150x150.png') }}" class="img-fluid" />
                                                @endif
                                            </div>
                                            <div class="col">
                                                <!-- Title -->
                                                <p class="mb-4 fs-sm fw-bold">
                                                    <a class="text-body" href="#">{{ $item->name }} x {{ $item->qty }}</a> <br>
                                                    <span class="text-body">Size: {{ $item->size }}</span> <br>
                                                    <span class="text-muted">${{ $item->total }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    <div class="card card-lg mb-5 mt-3">
                        <div class="card-body">
                            <!-- Heading -->
                            <h6 class="mt-0 mb-3 h5">Order Total</h6>

                            <!-- List group -->
                            <ul>
                                <li class="list-group-item d-flex">
                                    <span>Subtotal</span>
                                    <span class="ms-auto">${{ number_format($order->subtotal,2)  }}</span>
                                </li>
                                <li class="list-group-item d-flex">
                                    <span>Discount {{ (!empty($order->coupon_code)) ? '('.$order->coupon_code.')' : '' }}</span>
                                    <span class="ms-auto">${{ number_format($order->discount,2) }}</span>
                                </li>
                                <li class="list-group-item d-flex">
                                    <span>Shipping</span>
                                    <span class="ms-auto">${{ number_format($order->shipping,2) }}</span>
                                </li>
                                <li class="list-group-item d-flex fs-lg fw-bold">
                                    <span>Grand Total</span>
                                    <span class="ms-auto">${{ number_format($order->grand_total,2) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('customJS')
    <script>
        $(document).ready(function() {
            $('#cancelOrderForm').on('submit', function(e) {
                e.preventDefault();

                var orderId = $('#orderId').val();
                var feedback = $('#feedback').val();
                var token = $('input[name="_token"]').val();

                $.ajax({
                    url: '{{ route("order.cancel") }}',
                    type: 'POST',
                    data: {
                        _token: token,
                        order_id: orderId,
                        feedback: feedback
                    },
                    success: function(response) {
                        if (response.status) {
                            alert(response.message);
                            // Xóa đơn hàng khỏi giao diện người dùng
                            $('#order-' + orderId).remove();
                            $('#cancelOrderModal').modal('hide');
                            window.location.href= "{{ route("account.orders") }}"
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Error cancelling order');
                    }
                });
            });
        });
    </script>


@endsection
