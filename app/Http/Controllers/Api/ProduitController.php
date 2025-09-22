<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produit;

class ProduitController extends Controller
{
    public function show($id)
    {
        $produit = Produit::find($id);

        if (!$produit) {
            return response()->json(["message" => "Produit introuvable"], 404);
        }

        return response()->json($produit);
    }
}
