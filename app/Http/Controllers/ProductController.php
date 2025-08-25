<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return Product::all()->map(function ($product) {
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
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'offer_price' => 'nullable|numeric',
            'stock' => 'required|integer',
            'image' => 'required|array',
            'image.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            'category' => 'required|string',
        ]);

        $imagePaths = [];
        foreach ($request->file('image') as $image) {
            $path = $image->store('products', 'public');
            $imagePaths[] = Storage::url($path);
        }

        $product = Product::create([
            'seller_id' => auth()->id(),
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'offer_price' => $request->offer_price,
            'stock' => $request->stock,
            'image' => $imagePaths,
            'category' => $request->category,
        ]);

        return response()->json([
            '_id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => $product->price,
            'offerPrice' => $product->offer_price,
            'stock' => $product->stock,
            'image' => $product->image,
            'category' => $product->category,
        ], 201);
    }

    public function show(Product $product)
    {
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
    }

    public function update(Request $request, Product $product)
    {
        if ($product->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric',
            'offer_price' => 'nullable|numeric',
            'stock' => 'required|integer',
            'image' => 'nullable|array',
            'image.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            'category' => 'required|string',
        ]);

        $imagePaths = $product->image;
        if ($request->hasFile('image')) {
            foreach ($imagePaths as $path) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $path));
            }
            $imagePaths = [];
            foreach ($request->file('image') as $image) {
                $path = $image->store('products', 'public');
                $imagePaths[] = Storage::url($path);
            }
        }

        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'offer_price' => $request->offer_price,
            'stock' => $request->stock,
            'image' => $imagePaths,
            'category' => $request->category,
        ]);

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
    }

    public function destroy(Product $product)
    {
        if ($product->seller_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        foreach ($product->image as $path) {
            Storage::disk('public')->delete(str_replace('/storage/', '', $path));
        }

        $product->delete();

        return response()->json(null, 204);
    }

    public function sellerProducts()
    {
        $user = auth()->user();
        if ($user->role !== 'seller') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $user->products->map(function ($product) {
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
        });
    }
}