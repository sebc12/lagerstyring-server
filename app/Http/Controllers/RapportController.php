<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class RapportController extends Controller
{
    public function index()
    {
        $inventory = Inventory::with('details')->get();

        return response()->json($inventory);
    }
}
