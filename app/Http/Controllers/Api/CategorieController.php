<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Categorie;

class CategorieController extends Controller
{
    public function index()
    {
        // Retourner toutes les catégories avec leurs produits
        return Categorie::with('produits')->get();
    }

    public function show($id)
    {
        $categorie = Categorie::with('produits')->findOrFail($id);
        return response()->json($categorie);
    }

}
