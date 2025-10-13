<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductController extends Controller
{
    public function ajoutProduit(Request $request)
    {
        // Debug pour voir ce qui arrive
        //Log::info("Reçu:", $request->all());
        //Log::info("Fichier:", [$request->file("imageProduct")]);

        $request->validate([
            "refProduct" => "required|unique:products",
            "designProduct" => "required|string",
            "marqueProduct" => "required|string",
            "description" => "nullable|string",
            "stock" => "required|integer",
            "idSousCategorie" => "required|integer|exists:sous_categorie_produits,idSousCategorie",
            "idFournisseur" => "required|integer|exists:fournisseurs,idFournisseur",
            "imageProduct" => "nullable|file|max:2048",
        ]);

        // Upload image si présente
        $imagePath = null;
        if ($request->hasFile("imageProduct")) {
            $imagePath = $request->file("imageProduct")->store("produit", "public");
        }


        // Créer le produit
        $product = Product::create([
            "refProduct" => $request->refProduct,
            "designProduct" => $request->designProduct,
            "marqueProduct" => $request->marqueProduct,
            "description" => $request->description,
            "stock" => $request->stock,
            "idSousCategorie" => $request->idSousCategorie,
            "idFournisseur" => $request->idFournisseur,
            "imageProduct" => $imagePath,
        ]);

        return response()->json([
            "success" => true,
            "message" => "Produit ajouté avec succès",
            "data" => $product,
        ]);
    }

    public function getProduitsByFournisseur($idFournisseur)
    {
        // Récupère tous les produits liés à ce fournisseur
        $produits = Product::where('idFournisseur', $idFournisseur)->get();

        if ($produits->isEmpty()) {
            return response()->json([
                "success" => false,
                "message" => "Aucun produit trouvé pour ce fournisseur"
            ], 404);
        }

        return response()->json($produits);
    }
    public function index()
    {
        // Charger les produits avec leurs sous-catégories et catégories
        $produits = Product::with(['sousCategorie.categorie'])->get();

        $result = $produits->map(function ($p) {
            return [
                "id" => $p->id,
                "designProduct" => $p->designProduct,
                "price" => $p->price ?? null, // si tu as une colonne 'price', sinon tu peux la retirer
                "imageProduct" => $p->imageProduct,
                "sous_categorie" => $p->sousCategorie ? [
                    "nomSousCategorie" => $p->sousCategorie->nomSousCategorie,
                ] : null,
                "categorie" => $p->sousCategorie && $p->sousCategorie->categorie ? [
                    "nomCategorie" => $p->sousCategorie->categorie->nomCategorie,
                ] : null,
            ];
        });

        return response()->json($result);
    }



    public function show($id)
    {
        $produit = Product::find($id); // <-- Product et non Produit

        if (!$produit) {
            return response()->json(["message" => "Produit introuvable"], 404);
        }

        return response()->json($produit);
    }
}
