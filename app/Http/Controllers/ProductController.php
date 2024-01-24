<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Inventory;
use Illuminate\Http\Request;
use App\Models\ProductDetails;

class ProductController extends Controller
{
    public function store(Request $request)
    {

        $request->validate([
            'ProductID' => 'required|numeric',
            'ProductName' => 'required|string',
            'Price' => 'required|numeric',
            'Color' => 'nullable | string',
            'Storage' => 'nullable | numeric',
        ]);


        $productData = $request->only(['ProductID', 'ProductName', 'Price', 'Color', 'Storage']);

        $product = Product::create($productData);

        if ($product) {
            return response()->json(['message' => 'Product added successfully', 'product' => $product], 201);
        } else {
            return response()->json(['message' => 'Failed to create Product'], 500);
        }
    }

    public function addDetails(Request $request, $productId)
    {
        $request->validate([
            'SerialNumber' => 'nullable|string',
            'LocationID' => 'required|integer',
            'Quantity' => 'required|integer',
        ]);

        $product = Product::find($productId);

        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        //SerialNumber value or null
        $serialNumber = $request->input('SerialNumber', null);


        //Find existing inventory
        $existingInventory = Inventory::where('ProductID', $product->ProductID)
            ->where('SerialNumberID', null)
            ->where('LocationID', $request->input('LocationID'))
            ->first();

        if ($existingInventory) {
            $existingInventory->update([
                'Quantity' => $existingInventory->Quantity + $request->input('Quantity'),
            ]);
        } else {
            $productDetails = $serialNumber
                ? ProductDetails::create([
                    'ProductID' => $product->ProductID,
                    'SerialNumber' => $serialNumber,
                ])
                : null;

            Inventory::create([
                'ProductID' => $product->ProductID,
                'LocationID' => $request->input('LocationID'),
                'SerialNumberID' => optional($productDetails)->SerialNumberID,
                'Quantity' => $request->input('Quantity'),
            ]);
        }

        return response()->json(['message' => 'Successfully added'], 201);
    }
}
