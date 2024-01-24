<?php

namespace App\Http\Controllers;

use App\Models\Product;

use Illuminate\Http\Request;
use App\Models\ProductDetails;


class SearchController extends Controller
{
    public function index(Request $request)
    {
        try {
            $id = $request->input('id');
            $name = $request->input('name');
            $serialNumber = $request->input('serialNumber');


            $products = Product::with(['details.inventory.location'])
                ->when($name, function ($query) use ($name) {
                    return $query->where('ProductName', 'like', "%$name%");
                })
                ->when($id, function ($query) use ($id) {
                    return $query->where('ProductID', $id);
                })
                ->when($serialNumber, function ($query) use ($serialNumber) {
                    $query->whereHas('details', function ($subquery) use ($serialNumber) {
                        $subquery->where('SerialNumber', $serialNumber);
                    });
                })
                ->get();


            $productsWithoutDetails = Product::has('details', '=', 0)
                ->with(['inventory.location'])
                ->when($id, function ($query) use ($id) {
                    return $query->where('ProductID', $id);
                })
                ->when($name, function ($query) use ($name) {
                    return $query->where('ProductName', 'like', "%$name%");
                })
                ->when($serialNumber, function ($query) use ($serialNumber) {
                    $query->whereHas('details', function ($subquery) use ($serialNumber) {
                        $subquery->where('SerialNumber', $serialNumber);
                    });
                })
                ->get();

            // Merge the results
            $mergedProducts = $products->merge($productsWithoutDetails);

            if ($mergedProducts->isEmpty()) {
                return response()->json(['error' => 'No products found'], 404);
            }

            return response()->json([
                'products' => $mergedProducts,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'An error occurred.', 'message' => $e->getMessage()], 500);
        }
    }
}
