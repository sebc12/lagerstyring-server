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
        $request->validate([
            'ProductID' => 'required|numeric',
            'SerialNumber' => 'nullable|string',
            'FromLocationID' => 'required|numeric',
            'ToLocationID' => 'required|numeric',
            'QuantityMoved' => 'required|numeric',
        ]);

        $movedData = $request->only(['ProductID', 'SerialNumber', 'FromLocationID', 'ToLocationID', 'QuantityMoved']);

        try {
            $serialNumberId = $this->getSerialNumberId($movedData['SerialNumber']);

            $movedData['SerialNumberID'] = $serialNumberId;

            $transaktion = Transaktion::create($movedData);

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

        return response()->json(['message' => 'Products moved successfully'], 200);
    }


    private function getSerialNumberId($serialNumber)
    {
        if ($serialNumber === null) {
            return null;
        }

        $productDetails = ProductDetails::where('serialnumber', $serialNumber)->first();

        if (!$productDetails) {
            return response()->json(['error' => 'Serialnumber not found'], 404);
        }


        return $productDetails->SerialNumberID;
    }

    private function updateInventory($productId, $serialNumberId, $fromLocation, $toLocation, $quantityMoved)
    {
        $fromInventoryQuery = Inventory::where('ProductID', $productId)
            ->where('LocationID', $fromLocation);

        if ($serialNumberId) {
            $fromInventoryQuery->where('SerialNumberID', $serialNumberId);
        } else {
            $fromInventoryQuery->whereNull('SerialNumberID');
        }

        $fromInventory = $fromInventoryQuery->first();

        // Update the quantity in the from location
        if ($fromInventory) {
            $fromInventory->decrement('Quantity', $quantityMoved);
        }

        // Find or create the inventory record for the to location
        $toInventoryQuery = Inventory::where('ProductID', $productId)
            ->where('LocationID', $toLocation);

        if ($serialNumberId) {
            $toInventoryQuery->where('SerialNumberID', $serialNumberId);
        } else {
            $toInventoryQuery->whereNull('SerialNumberID');
        }

        $toInventory = $toInventoryQuery->firstOrNew();

        if (!$toInventory->exists) {
            $toInventory->ProductID = $productId;
            $toInventory->SerialNumberID = $serialNumberId;
            $toInventory->LocationID = $toLocation;
            $toInventory->Quantity = $quantityMoved;
        } else {
            $toInventory->increment('Quantity', $quantityMoved);
        }


        $toInventory->save();
    }
}
