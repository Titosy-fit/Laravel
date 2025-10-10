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


    public function show($id)
    {
        $produit = Product::find($id); // <-- Product et non Produit

        if (!$produit) {
            return response()->json(["message" => "Produit introuvable"], 404);
        }

        return response()->json($produit);
    }
}
