<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class SellerController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        if ($user->role !== 'seller') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $profile = $user->sellerProfile;
        $products = $user->products;
        $orders = Order::whereHas('items', function ($query) use ($user) {
            $query->whereIn('product_id', $user->products->pluck('id'));
        })->with('items.product', 'address')->get()->map(function ($order) {
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
                    'fullName' => $order->address->full_name,
                    'area' => $order->address->area,
                    'city' => $order->address->city,
                    'state' => $order->address->state,
                    'phoneNumber' => $order->address->phone_number,
                ],
                'amount' => $order->total_price,
                'date' => $order->created_at->toDateTimeString(),
                'payment' => $order->status === 'delivered' ? 'Completed' : 'Pending',
            ];
        });

        return response()->json([
            'profile' => [
                'shop_name' => $profile->shop_name,
                'shop_description' => $profile->shop_description,
            ],
            'products' => $products->map(function ($product) {
                return [
                    '_id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => $product->price,
                    'offerPrice' => $product->offer_price,
                    'stock' => $product->stock,
                    'image' => $product->image,
                    'category' => $product->category,
                ];
            }),
            'orders' => $orders,
        ]);
    }

    public function orders()
    {
        $user = auth()->user();
        if ($user->role !== 'seller') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $orders = Order::whereHas('items', function ($query) use ($user) {
            $query->whereIn('product_id', $user->products->pluck('id'));
        })->with('items.product', 'address')->get()->map(function ($order) {
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
                    'fullName' => $order->address->full_name,
                    'area' => $order->address->area,
                    'city' => $order->address->city,
                    'state' => $order->address->state,
                    'phoneNumber' => $order->address->phone_number,
                ],
                'amount' => $order->total_price,
                'date' => $order->created_at->toDateTimeString(),
                'payment' => $order->status === 'delivered' ? 'Completed' : 'Pending',
            ];
        });

        return response()->json($orders);
    }
}