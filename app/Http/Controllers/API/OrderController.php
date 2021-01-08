<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class OrderController extends Controller
{
    public function index(Request $req) {
        $userId = $req->user_id;
        $orders = Order::query();

        $orders->when($userId, function($q) use ($userId) {
            return $q->where('user_id', $userId);
        });

        return response()->json([
            'status' => 'success',
            'data' => $orders->get()
        ]);
    }

    public function store(Request $req) {
        $user = $req->user;
        $course = $req->course;

        $order = Order::create([
            'user_id' => $user['id'],
            'course_id' => $course['id']
        ]);

        $transactionDetails = [
            'order_id' => $order->id . '-' . Str::random(5),
            'gross_amount' => $course['price']
        ];

        $itemDetails = [
            [
                'id' => $course['id'],
                'price' => $course['price'],
                'quantity' => 1,
                'name' => $course['name'],
                'brand' => 'BowieTech',
                'category' => 'Online Course'
            ]
        ];

        $customerDetails = [
            'first_name' => $user['name'],
            'email' => $user['email'],
        ];

        $midtransParams = [
            'transaction_details' => $transactionDetails,
            'item_details' => $itemDetails,
            'customer_details' => $customerDetails
        ];

        $midtransSnapUrl = $this->getMidTransSnapUrl($midtransParams);
        
        $order->snap_url = $midtransSnapUrl;
        $order->metadata = [
            'course_id' => $course['id'],
            'course_price' => $course['price'],
            'course_name' => $course['name'],
            'course_thumbnail' => $course['thumbnail'],
            'course_level' => $course['leve']
        ];
        $order->save();

        return response()->json([
            'status' => 'success',
            'data' => $order
        ]);
    }

    private function getMidTransSnapUrl($params) {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = (bool) env('MIDTRANS_PRODUCTION');
        Config::$is3ds = (bool) env('MIDTRANS_3DS');

        $snapUrl = Snap::createTransaction($params)->redirect_url;
        return $snapUrl;
    }
}
