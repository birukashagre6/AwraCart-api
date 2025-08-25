<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index()
    {
        $orders = auth()->user()->orders()->with('items.product', 'address')->get()->map(function ($order) {
            return [
                '_id' => $order->id,
                'items' => $order->items->map(function ($item) {
                    return [
                        'product' => [
                            '_id' => $item->product->id,
                            'name' => $item->product->name,
                            'image' => $item->product->image,
                        ],
                        'quantity' => $item->quantity,
                    ];
                }),
                'address' => [
                    '_id' => $order->address->id,
                    'fullName' => $order->address->full_name,
                    'area' => $order->address->area,
                    'city' => $order->address->city,
                    'state' => $order->address->state,
                    'phoneNumber' => $order->address->phone_number,
                ],
                'amount' => $order->total_price,
                'date' => $order->created_at->toDateTimeString(),
                'status' => $order->status,
            ];
        });

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'address_id' => 'required|exists:addresses,id',
        ]);

        $cart = auth()->user()->cart()->with('items.product')->first();
        if (!$cart || !$cart->items()->exists()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $totalPrice = $cart->items->sum(function ($item) {
            return $item->product->offer_price * $item->quantity;
        });

        $order = Order::create([
            'user_id' => auth()->id(),
            'address_id' => $request->address_id,
            'total_price' => $totalPrice,
            'status' => 'pending',
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->product->offer_price,
            ]);
        }

        $cart->items()->delete();

        return response()->json([
            '_id' => $order->id,
            'items' => $order->items()->with('product')->get()->map(function ($item) {
                return [
                    'product' => [
                        '_id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->image,
                    ],
                    'quantity' => $item->quantity,
                ];
            }),
            'address' => [
                '_id' => $order->address->id,
                'fullName' => $order->address->full_name,
                'area' => $order->address->area,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'phoneNumber' => $order->address->phone_number,
            ],
            'amount' => $order->total_price,
            'date' => $order->created_at->toDateTimeString(),
            'status' => $order->status,
        ], 201);
    }

    public function show(Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return [
            '_id' => $order->id,
            'items' => $order->items()->with('product')->get()->map(function ($item) {
                return [
                    'product' => [
                        '_id' => $item->product->id,
                        'name' => $item->product->name,
                        'image' => $item->product->image,
                    ],
                    'quantity' => $item->quantity,
                ];
            }),
            'address' => [
                '_id' => $order->address->id,
                'fullName' => $order->address->full_name,
                'area' => $order->address->area,
                'city' => $order->address->city,
                'state' => $order->address->state,
                'phoneNumber' => $order->address->phone_number,
            ],
            'amount' => $order->total_price,
            'date' => $order->created_at->toDateTimeString(),
            'status' => $order->status,
        ];
    }
}