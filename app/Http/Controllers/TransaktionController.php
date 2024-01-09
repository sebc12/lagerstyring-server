<?php

namespace App\Http\Controllers;


use App\Models\Inventory;
use App\Models\Transaktion;
use Illuminate\Http\Request;
use App\Models\ProductDetails;


class TransaktionController extends Controller
{
    public function moveProducts(Request $request)
    {
        // Validate the request data
        $request->validate([
            'ProductID' => 'required|numeric',
            'SerialNumber' => 'nullable|string',
            'FromLocationID' => 'required|numeric',
            'ToLocationID' => 'required|numeric',
            'QuantityMoved' => 'required|numeric',
        ]);

        // Get the input data
        $movedData = $request->only(['ProductID', 'SerialNumber', 'FromLocationID', 'ToLocationID', 'QuantityMoved']);

        try {
            // Find or create the SerialNumberID based on the provided serial number
            $serialNumberId = $this->getSerialNumberId($movedData['SerialNumber']);

            // Add SerialNumberID to the data array
            $movedData['SerialNumberID'] = $serialNumberId;

            // Create the Transaktion record
            $transaktion = Transaktion::create($movedData);

            // Update the inventory
            $this->updateInventory(
                $transaktion->ProductID,
                $transaktion->SerialNumberID,
                $transaktion->FromLocationID,
                $transaktion->ToLocationID,
                $transaktion->QuantityMoved
            );
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        // Return a consistent success response
        return response()->json(['message' => 'Products moved successfully'], 200);
    }


    private function getSerialNumberId($serialNumber)
    {
        // If serial number is null, return null
        if ($serialNumber === null) {
            return null;
        }

        // Find a ProductDetails record based on the provided serial number
        $productDetails = ProductDetails::where('serialnumber', $serialNumber)->first();

        // If the record is not found, throw an exception
        if (!$productDetails) {
            throw new \Exception("Serial number '$serialNumber' not found in the database.");
        }

        // Return the SerialNumberID from the found record
        return $productDetails->SerialNumberID;
    }

    private function updateInventory($productId, $serialNumberId, $fromLocation, $toLocation, $quantityMoved)
    {
        // Find the inventory record for the product and serial number at the from location
        $fromInventoryQuery = Inventory::where('ProductID', $productId)
            ->where('LocationID', $fromLocation);

        if ($serialNumberId) {
            $fromInventoryQuery->where('SerialNumberID', $serialNumberId);
        } else {
            // If no serial number, consider all products without serial numbers
            $fromInventoryQuery->whereNull('SerialNumberID');
        }

        $fromInventory = $fromInventoryQuery->first();

        // Update the quantity in the from location
        if ($fromInventory) {
            $fromInventory->decrement('Quantity', $quantityMoved);
        }

        // Find or create the inventory record for the product and serial number at the to location
        $toInventoryQuery = Inventory::where('ProductID', $productId)
            ->where('LocationID', $toLocation);

        if ($serialNumberId) {
            $toInventoryQuery->where('SerialNumberID', $serialNumberId);
        } else {
            // If no serial number, consider all products without serial numbers
            $toInventoryQuery->whereNull('SerialNumberID');
        }

        $toInventory = $toInventoryQuery->firstOrNew();

        // Set the ProductID and other necessary values
        $toInventory->ProductID = $productId;
        $toInventory->SerialNumberID = $serialNumberId;
        $toInventory->LocationID = $toLocation;

        // If the toInventory record is new (created), set the quantity directly
        if (!$toInventory->exists) {
            $toInventory->Quantity = $quantityMoved;
        } else {
            // If the toInventory record already exists, increment the quantity
            $toInventory->increment('Quantity', $quantityMoved);
        }


        $toInventory->save();
    }
}
