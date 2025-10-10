<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SousCategorieProduit;
use App\Models\CategorieProduit; // si tu veux accéder à la relation


class SousCategorieProduitController extends Controller
{
    public function index()
    {
        return SousCategorieProduit::with("categorieProduit")->get();
    }
    // Récupérer toutes les sous-catégories d'une catégorie spécifique
    public function getByCategorie($idCategorie)
    {
        $sousCategories = SousCategorieProduit::where('categorieProduit_id', $idCategorie)->get();

        return response()->json($sousCategories, 200);
    }

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            "nomSousCategorie" => "required|string|max:255",
            "imageSousCategorie" => "required|image|mimes:jpg,jpeg,png|max:2048",
            "categorieProduit_id" => "required|exists:categorie_produits,id",
        ]);

        // Stockage du fichier dans storage/app/public/souscategories
        $path = $request->file("imageSousCategorie")->store("souscategories", "public");

        // Création de la sous-catégorie
        $sousCategorie = SousCategorieProduit::create([
            "nomSousCategorie" => $request->nomSousCategorie,
            "imageSousCategorie" => $path,
            "categorieProduit_id" => $request->categorieProduit_id,
        ]);

        return response()->json($sousCategorie, 201);
    }

    // Nouvelle méthode pour récupérer une sous-catégorie avec ses produits
    public function show($idSousCat)
    {
        $sousCategorie = SousCategorieProduit::with("products")->findOrFail($idSousCat);
        return response()->json($sousCategorie);
    }
}
