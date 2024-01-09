<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\TransaktionController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/product', [ProductController::class, 'store']);

Route::post('/product/{productId}', [ProductController::class, 'addDetails']);


Route::get('/search', [SearchController::class, 'index']);

Route::post('/move-product', [TransaktionController::class, 'moveProducts']);




Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
