<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SousCategorieProduit;

class SousCategorieProduitController extends Controller
{
    // Lister toutes les sous-catégories avec la catégorie parent
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

    // Ajouter une sous-catégorie
    public function store(Request $request)
    {
        $request->validate([
            "nomSousCategorie" => "required|string|max:255",
            "imageSousCategorie" => "required|image|mimes:jpg,jpeg,png|max:2048",
            "categorieProduit_id" => "required|exists:categorie_produits,id",
        ]);

        $path = $request->file("imageSousCategorie")->store("souscategories", "public");

        $sousCategorie = SousCategorieProduit::create([
            "nomSousCategorie" => $request->nomSousCategorie,
            "imageSousCategorie" => $path,
            "categorieProduit_id" => $request->categorieProduit_id,
        ]);

        return response()->json($sousCategorie, 201);
    }

    // Récupérer une sous-catégorie avec ses produits
    public function show($idSousCat)
    {
        $sousCategorie = SousCategorieProduit::with("products")->findOrFail($idSousCat);
        return response()->json($sousCategorie);
    }

    // Modifier une sous-catégorie
    public function update(Request $request, $idSousCat)
    {
        $sousCategorie = SousCategorieProduit::find($idSousCat);
        if (!$sousCategorie) return response()->json(['message' => 'Sous-catégorie non trouvée'], 404);

        $request->validate([
            "nomSousCategorie" => "required|string|max:255",
            "imageSousCategorie" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
            "categorieProduit_id" => "required|exists:categorie_produits,id",
        ]);

        $sousCategorie->nomSousCategorie = $request->nomSousCategorie;
        $sousCategorie->categorieProduit_id = $request->categorieProduit_id;

        if ($request->hasFile("imageSousCategorie")) {
            $path = $request->file("imageSousCategorie")->store("souscategories", "public");
            $sousCategorie->imageSousCategorie = $path;
        }

        $sousCategorie->save();

        return response()->json(['message' => 'Sous-catégorie modifiée', 'data' => $sousCategorie], 200);
    }

    // Supprimer une sous-catégorie
    public function destroy($idSousCat)
    {
        $sousCategorie = SousCategorieProduit::find($idSousCat);
        if (!$sousCategorie) return response()->json(['message' => 'Sous-catégorie non trouvée'], 404);

        $sousCategorie->delete();
        return response()->json(['message' => 'Sous-catégorie supprimée'], 200);
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');
        $categorieId = $request->input('categorie_id', null);

        // Requête principale
        $sousCategories = SousCategorieProduit::query()
            ->when($categorieId, function ($q) use ($categorieId) {
                $q->where('categorieProduit_id', $categorieId);
            })
            ->when($query, function ($q) use ($query) {
                $q->where('nomSousCategorie', 'like', "%{$query}%");
            })
            ->limit(10)
            ->get(['idSousCategorie', 'nomSousCategorie', 'imageSousCategorie', 'categorieProduit_id']);

        return response()->json($sousCategories);
    }
}