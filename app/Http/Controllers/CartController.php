<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = auth()->user()->cart()->with('items.product')->firstOrCreate(['user_id' => auth()->id()]);

        $cartItems = [];
        foreach ($cart->items as $item) {
            $cartItems[$item->product_id] = $item->quantity;
        }

        return response()->json([
            'cartItems' => $cartItems,
            'items' => $cart->items->map(function ($item) {
                return [
                    'product_id' => $item->product_id,
                    'quantity' => $item->quantity,
                    'product' => [
                        '_id' => $item->product->id,
                        'name' => $item->product->name,
                        'price' => $item->product->price,
                        'offerPrice' => $item->product->offer_price,
                        'image' => $item->product->image,
                    ],
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = auth()->user()->cart()->firstOrCreate(['user_id' => auth()->id()]);

        $cartItem = $cart->items()->updateOrCreate(
            ['product_id' => $request->product_id],
            ['quantity' => $request->quantity]
        );

        $cartItems = [];
        foreach ($cart->items as $item) {
            $cartItems[$item->product_id] = $item->quantity;
        }

        return response()->json(['cartItems' => $cartItems], 201);
    }

    public function destroy($productId)
    {
        $cart = auth()->user()->cart;
        if ($cart) {
            $cart->items()->where('product_id', $productId)->delete();
        }

        $cartItems = [];
        if ($cart) {
            foreach ($cart->items as $item) {
                $cartItems[$item->product_id] = $item->quantity;
            }
        }

        return response()->json(['cartItems' => $cartItems]);
    }
}