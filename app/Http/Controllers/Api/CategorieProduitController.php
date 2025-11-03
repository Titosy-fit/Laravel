<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CategorieProduit;
use App\Models\SousCategorieProduit;
use App\Models\Product;

class CategorieProduitController extends Controller
{
    // Liste des catégories
    public function index()
    {
        return response()->json(CategorieProduit::all(), 200);
    }

    public function afichage()
    {
        // Charger catégories → sous-catégories → produits
        $categories = CategorieProduit::with("sousCategories.products")->get();

        // Filtrer les sous-catégories sans produits
        $categories->each(function ($cat) {
            $cat->sousCategories = $cat->sousCategories->filter(function ($sousCat) {
                return $sousCat->products->isNotEmpty();
            })->values(); // réindexer
        });

        return response()->json($categories);
    }

    // Ajouter une catégorie
    public function store(Request $request)
    {
        //dd($request->all(), $request->file('photoCategorie'));

        $request->validate([
            'nomCategorie' => 'required|string|max:255',
             'photoCategorie' => 'nullable|file'
        ]);

        $categorie = new CategorieProduit();
        $categorie->nomCategorie = $request->nomCategorie;

        if ($request->hasFile('photoCategorie')) {
            $file = $request->file('photoCategorie');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('categories', $filename, 'public');
            $categorie->photoCategorie = $path;
        }

        $categorie->save();

        return response()->json([
            'message' => 'Catégorie ajoutée avec succès',
            'data' => $categorie
        ], 201);
    }
    public function getCategoriesWithProducts()
    {
    try {
        $categories = CategorieProduit::with(['sousCategories.products'])
            ->whereHas('sousCategories.products')
            ->get();

        $result = $categories->map(function ($categorie) {
            return [
                'idCategorie' => $categorie->idCategorie,
                'nomCategorie' => $categorie->nomCategorie,
                'sousCategories' => $categorie->sousCategories->map(function ($sousCategorie) {
                    return [
                        'idSousCategorie' => $sousCategorie->idSousCategorie,
                        'nomSousCategorie' => $sousCategorie->nomSousCategorie,
                        'productCount' => $sousCategorie->products->count()
                    ];
                })
            ];
        });

        return response()->json($result);
    } catch (\Exception $e) {
        return response()->json([], 500);
    }
    }

    public function update(Request $request, $id) {
        $categorie = CategorieProduit::find($id);
        if (!$categorie) return response()->json(['message' => 'Non trouvé'], 404);
    
        $request->validate([
            'nomCategorie' => 'required|string|max:255',
            'photoCategorie' => 'nullable|file'
        ]);
    
        $categorie->nomCategorie = $request->nomCategorie;
    
        if ($request->hasFile('photoCategorie')) {
            $file = $request->file('photoCategorie');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('categories', $filename, 'public');
            $categorie->photoCategorie = $path;
        }
    
        $categorie->save();
        return response()->json(['message' => 'Catégorie modifiée', 'data' => $categorie], 200);
    }
    
    public function destroy($id) {
    $categorie = CategorieProduit::find($id);
    if (!$categorie) return response()->json(['message' => 'Non trouvé'], 404);

    $categorie->delete();
    return response()->json(['message' => 'Catégorie supprimée'], 200);
    }
}
