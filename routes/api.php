<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CategorieController;


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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
Route::get('/categories', [App\Http\Controllers\Api\CategorieController::class, 'index']);
Route::get("/produits/{id}", [ProduitController::class, "show"]);
Route::post("/clients/register", [ClientController::class, "register"]);
Route::post("/clients/login", [ClientController::class, "login"]);
Route::get('/categories/{id}', [CategorieController::class, 'show']);
