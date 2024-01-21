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

        if ($product) {
            $productDetailsData = $request->only(['SerialNumber']);

            $productDetailsSerialNumber = $productDetailsData['SerialNumber'] ?? null;

            $existingInventoryEntry = Inventory::where('ProductID', $product->ProductID)
                ->where('SerialNumberID', null)
                ->where('LocationID', $request->input('LocationID'))
                ->first();

            if ($existingInventoryEntry) {
                $existingInventoryEntry->update([
                    'Quantity' => $existingInventoryEntry->Quantity + $request->input('Quantity'),
                ]);
            } else {
                $productDetails = null;
                if ($productDetailsSerialNumber !== null) {
                    $productDetails = new ProductDetails([
                        'ProductID' => $product->ProductID,
                        'SerialNumber' => $productDetailsSerialNumber,
                    ]);
                    $productDetails->save();
                }

                Inventory::create([
                    'ProductID' => $product->ProductID,
                    'LocationID' => $request->input('LocationID'),
                    'SerialNumberID' => $productDetails ? $productDetails->SerialNumberID : null,
                    'Quantity' => $request->input('Quantity'),
                ]);
            }

            return response()->json(['message' => 'Product details added successfully'], 201);
        } else {
            return response()->json(['message' => 'Product not found'], 404);
        }
    }
}
