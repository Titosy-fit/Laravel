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
use App\Http\Controllers\CommandeController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\Api\ApproController;
use App\Http\Controllers\SearchController;


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
Route::get('/produits/souscategorie/{idSousCategorie}', [ProductController::class, 'getBySousCategorie']);
Route::get('/souscategories/search', [SousCategorieProduitController::class, 'search']);



//login
Route::post("/login", [AuthController::class, "login"]);

// Produit
Route::post('/products', [ProductController::class, 'ajoutProduit']);
// Route::get('/products', [ProductController::class, 'index']);
// Route::get('/products', function () {
//     return \App\Models\Product::all();
// });
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);

Route::get('/produits/{idProduct}/etat-stock', [ProductController::class, 'getEtatStock']);
Route::get('/fournisseur/{idFournisseur}/etat-stock-global', [ProductController::class, 'getEtatStockGlobal']);
// Route pour modifier un produit
Route::put('/produits/{idProduct}/modifier', [ProductController::class, 'updateProduit']);
// Modification de l'image seulement
Route::put('/produits/{idProduct}/modifier-image', [ProductController::class, 'updateImage'])->name('produits.updateImage');


// Nouvel endpoint pour les produits avec catégories (pour le frontend)
Route::get('/products-with-categories', [ProductController::class, 'indexWithCategories']);
Route::get('/fournisseur/{id}/produits', [ProductController::class, 'getProduitsByFournisseur']);
Route::get("/product/{id}", [ProductController::class, "show"]);
Route::delete('products/{id}', [ProductController::class, 'destroy']);
Route::post('/produits/{idProduct}/nouveau-prix', [ProductController::class, 'ajoutNouveauPrix']);
Route::get('/produits/{idProduct}/historique-prix', [ProductController::class, 'getHistoriquePrix']);



// Endpoint API
Route::get("/categories-produits", [CategorieProduitController::class, "afichage"]);
// Ajoutez ces routes pour les catégories et suggestions
Route::get('/categories-with-products', [CategorieProduitController::class, 'getCategoriesWithProducts']);
Route::get('/search-suggestions', [ProductController::class, 'getSearchSuggestions']);
Route::get('/products-with-categories', [ProductController::class, 'indexWithCategories']);
Route::post('/commandes', [CommandeController::class, 'store']);
//admin

Route::post('/admin/login', [AdminAuthController::class, 'login']);
Route::post('/admin/logout', [AdminAuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/admin/me', function (Request $request) {
    return response()->json([
        'nomAdmin' => $request->user()->name,
        'emailAdmin' => $request->user()->email,
    ]);
});
Route::get('/admin/info', [AdminAuthController::class, 'getInfo']);
Route::post('/admin/update-password', [AdminAuthController::class, 'updatePassword']);


// Approvisionnements
Route::get('/approvisionnements', [ApproController::class, 'index']);
Route::post('/approvisionnements/ajout', [ApproController::class, 'store']);
Route::get('/approvisionnements/{id}', [ApproController::class, 'show']);
Route::put('/approvisionnements/{id}', [ApproController::class, 'update']);
Route::delete('/approvisionnements/{id}', [ApproController::class, 'destroy']);