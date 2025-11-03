<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Commande;

class CommandeController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'idClient' => 'required|exists:clients,id',
            'produits' => 'required|array',
            'total' => 'required|numeric',
        ]);

        try {
            $commande = Commande::create([
                'idClient' => $request->idClient,
                'produits' => json_encode($request->produits),
                'total' => $request->total,
                'status' => 'en attente'
            ]);

            return response()->json([
                'message' => 'Commande créée',
                'commande' => $commande
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
