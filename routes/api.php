<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProduitController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\CategorieController;
use App\Http\Controllers\Api\LivreurController;
use App\Http\Controllers\Api\FournisseurController;
use App\Http\Controllers\Api\CategorieProduitController;
use App\Http\Controllers\Api\SousCategorieProduitController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;


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

Route::get('/categories/{id}', [CategorieController::class, 'show']);

//Clients
Route::post("/clients/register", [ClientController::class, "register"]);
Route::post("/clients/login", [ClientController::class, "login"]);
Route::post("/clients/verify-email", [ClientController::class, "verifyEmail"]);

//Livreur
Route::post('/livreur/register', [LivreurController::class, 'register']);
Route::post('/livreur/verify-email', [LivreurController::class, 'verifyEmail']);
Route::get('/livreur/en-attente', [LivreurController::class, 'getEnAttente']);
Route::post('/livreur/{id}/etat', [LivreurController::class, 'updateEtat']);
Route::middleware('auth:sanctum')->post('/logoutLivreur', [LivreurController::class, 'logout']);
Route::middleware('auth:sanctum')->post('/livreur/change-password', [LivreurController::class, 'changePassword']);


//Fornisseur
Route::post('/fournisseur/register', [FournisseurController::class, 'register']);
Route::get('/fournisseurs/en-attente', [FournisseurController::class, 'getEnAttente']);
Route::post('/fournisseurs/{id}/etat', [FournisseurController::class, 'updateEtat']);
Route::post('/fournisseurs/verify-email', [FournisseurController::class, 'verifyEmail']);

//Categories
Route::get('/categoriesproduit', [CategorieProduitController::class, 'index']);
Route::post('/categoriesproduit', [CategorieProduitController::class, 'store']);


//Sous categories
Route::get("/souscategories", [SousCategorieProduitController::class, "index"]);
Route::post("/souscategories", [SousCategorieProduitController::class, "store"]);
Route::get('/souscategories/categorie/{idCategorie}', [SousCategorieProduitController::class, 'getByCategorie']);
Route::get('/souscategories/{idSousCat}', [SousCategorieProduitController::class, 'show']);
Route::delete('/souscategories/{idSousCat}', [SousCategorieProduitController::class, 'destroy']);


//login
Route::post("/login", [AuthController::class, "login"]);

// Produit
Route::post('/products', [ProductController::class, 'ajoutProduit']);
// Route::get('/products', [ProductController::class, 'index']);
Route::get('/products', function () {
    return \App\Models\Product::all();
});
Route::get('/fournisseur/{id}/produits', [ProductController::class, 'getProduitsByFournisseur']);
Route::get("/product/{id}", [ProductController::class, "show"]);

// Endpoint API
Route::get("/categories-produits", [CategorieProduitController::class, "afichage"]);
