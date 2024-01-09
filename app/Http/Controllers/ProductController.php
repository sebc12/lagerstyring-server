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
        // Validate the incoming request data for Product
        $request->validate([
            'ProductID' => 'required|numeric',
            'ProductName' => 'required|string',
            'Price' => 'required|numeric',
            'Color' => 'nullable | string',
            'Storage' => 'nullable | numeric',
        ]);


        $productData = $request->only(['ProductID', 'ProductName', 'Price', 'Color', 'Storage']);

        // Create a new product
        $product = Product::create($productData);

        // Make sure the product creation was successful
        if ($product) {
            // Return a response with the created product information
            return response()->json(['message' => 'Product added successfully', 'product' => $product], 201);
        } else {
            // Handle the case where creating Product failed
            return response()->json(['message' => 'Failed to create Product'], 500);
        }
    }

    public function addDetails(Request $request, $productId)
    {
        // Validate the incoming request data for ProductDetails
        $request->validate([
            'SerialNumber' => 'nullable|string',
            'LocationID' => 'required|integer',
            'Quantity' => 'required|integer',
        ]);

        // Find the product by ProductID
        $product = Product::find($productId);

        // Check if the product exists
        if ($product) {
            $productDetailsData = $request->only(['SerialNumber']);

            $productDetailsSerialNumber = $productDetailsData['SerialNumber'] ?? null;

            // Check if an entry already exists in the Inventory table
            $existingInventoryEntry = Inventory::where('ProductID', $product->ProductID)
                ->where('SerialNumberID', null)  // Check for entries without serial number
                ->where('LocationID', $request->input('LocationID'))
                ->first();

            if ($existingInventoryEntry) {
                // Update the quantity for an existing entry
                $existingInventoryEntry->update([
                    'Quantity' => $existingInventoryEntry->Quantity + $request->input('Quantity'),
                ]);
            } else {
                // Create ProductDetails and associate it with SerialNumber if present
                $productDetails = null;
                if ($productDetailsSerialNumber !== null) {
                    $productDetails = new ProductDetails([
                        'ProductID' => $product->ProductID,
                        'SerialNumber' => $productDetailsSerialNumber,
                    ]);
                    $productDetails->save();
                }

                // Create inventory entry
                Inventory::create([
                    'ProductID' => $product->ProductID,
                    'LocationID' => $request->input('LocationID'),
                    'SerialNumberID' => $productDetails ? $productDetails->SerialNumberID : null,
                    'Quantity' => $request->input('Quantity'),
                ]);
            }

            // Return a response
            return response()->json(['message' => 'Product details added successfully'], 201);
        } else {
            // Handle the case where the product with the given ID was not found
            return response()->json(['message' => 'Product not found'], 404);
        }
    }
}
