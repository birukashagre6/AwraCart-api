<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        return auth()->user()->addresses->map(function ($address) {
            return [
                '_id' => $address->id,
                'fullName' => $address->full_name,
                'phoneNumber' => $address->phone_number,
                'pincode' => $address->pincode,
                'area' => $address->area,
                'city' => $address->city,
                'state' => $address->state,
            ];
        });
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'pincode' => 'required|integer',
            'area' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
        ]);

        $address = auth()->user()->addresses()->create($request->all());

        return response()->json([
            '_id' => $address->id,
            'fullName' => $address->full_name,
            'phoneNumber' => $address->phone_number,
            'pincode' => $address->pincode,
            'area' => $address->area,
            'city' => $address->city,
            'state' => $address->state,
        ], 201);
    }

    public function show(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return [
            '_id' => $address->id,
            'fullName' => $address->full_name,
            'phoneNumber' => $address->phone_number,
            'pincode' => $address->pincode,
            'area' => $address->area,
            'city' => $address->city,
            'state' => $address->state,
        ];
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'phone_number' => 'required|string|max:20',
            'pincode' => 'required|integer',
            'area' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
        ]);

        $address->update($request->all());

        return [
            '_id' => $address->id,
            'fullName' => $address->full_name,
            'phoneNumber' => $address->phone_number,
            'pincode' => $address->pincode,
            'area' => $address->area,
            'city' => $address->city,
            'state' => $address->state,
        ];
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $address->delete();

        return response()->json(null, 204);
    }
}