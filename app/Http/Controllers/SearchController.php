<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SousCategorieProduit;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function suggestions(Request $request)
    {
        $query = trim($request->input('q', ''));
        if (empty($query)) {
            return response()->json(['products' => [], 'sousCategories' => []]);
        }

        // 🔍 Recherche produits partielle
        $products = Product::where('designProduct', 'LIKE', "%{$query}%")
            ->orWhere('marqueProduct', 'LIKE', "%{$query}%")
            ->take(5)
            ->get(['idProduct', 'designProduct', 'imageProduct', 'idSousCategorie']);

        // 🔍 Recherche sous-catégories correspondantes
        $sousCategories = SousCategorieProduit::where('nomSousCategorie', 'LIKE', "%{$query}%")
            ->withCount('products')
            ->take(5)
            ->get(['idSousCategorie', 'nomSousCategorie']);

        return response()->json([
            'products' => $products,
            'sousCategories' => $sousCategories
        ]);
    }
}
