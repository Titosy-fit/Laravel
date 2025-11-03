<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Approvisionnement;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ApproController extends Controller
{
    /**
     * Lister tous les approvisionnements
     */
    public function index()
    {
        $appros = Approvisionnement::with('product')->orderBy('id', 'desc')->get();
        return response()->json($appros, 200);
    }

    /**
     * Ajouter un nouvel approvisionnement
     */
    public function store(Request $request)
    {
        try {
            Log::info('Données reçues:', $request->all());
    
            // Validation
            $validated = $request->validate([
                'idProduct' => 'required|integer|exists:products,idProduct',
                'qteAppro' => 'required|numeric|min:0.1',
            ]);
    
            // Utilisation directe de DB si le modèle pose problème
            $approId = DB::table('approvisionnements')->insertGetId([
                'idProduct' => $validated['idProduct'],
                'qteAppro' => $validated['qteAppro'],
                'dateAppro' => now(),
            ]);
    
            // Mettre à jour le stock
            DB::table('products')
                ->where('idProduct', $validated['idProduct'])
                ->increment('stock', $validated['qteAppro']);
    
            // Récupérer l'approvisionnement avec le produit
            $appro = DB::table('approvisionnements as a')
                ->join('products as p', 'a.idProduct', '=', 'p.idProduct')
                ->where('a.id', $approId)
                ->select('a.*', 'p.designProduct', 'p.refProduct', 'p.imageProduct')
                ->first();
    
            Log::info('Approvisionnement créé avec succès:', (array)$appro);
    
            return response()->json([
                'message' => 'Approvisionnement ajouté avec succès',
                'data' => $appro
            ], 201);
    
        } catch (\Exception $e) {
            Log::error('Erreur:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Modifier un approvisionnement
     */
    public function update(Request $request, $id)
    {
        try {
            Log::info('Modification approvisionnement:', ['id' => $id, 'data' => $request->all()]);

            // Validation
            $validated = $request->validate([
                'qteAppro' => 'required|numeric|min:0.1',
            ]);

            // Récupérer l'ancien approvisionnement
            $oldAppro = DB::table('approvisionnements')
                ->where('id', $id)
                ->first();

            if (!$oldAppro) {
                return response()->json(['error' => 'Approvisionnement non trouvé'], 404);
            }

            // Calculer la différence de quantité
            $difference = $validated['qteAppro'] - $oldAppro->qteAppro;

            // Mettre à jour l'approvisionnement
            DB::table('approvisionnements')
                ->where('id', $id)
                ->update([
                    'qteAppro' => $validated['qteAppro'],
                    'dateAppro' => now(),
                ]);

            // Mettre à jour le stock du produit
            if ($difference != 0) {
                DB::table('products')
                    ->where('idProduct', $oldAppro->idProduct)
                    ->increment('stock', $difference);
            }

            // Récupérer l'approvisionnement mis à jour
            $updatedAppro = DB::table('approvisionnements as a')
                ->join('products as p', 'a.idProduct', '=', 'p.idProduct')
                ->where('a.id', $id)
                ->select('a.*', 'p.designProduct', 'p.refProduct', 'p.imageProduct')
                ->first();

            Log::info('Approvisionnement modifié avec succès:', (array)$updatedAppro);

            return response()->json([
                'message' => 'Approvisionnement modifié avec succès',
                'data' => $updatedAppro
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur modification:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un approvisionnement
     */
    public function destroy($id)
    {
        try {
            Log::info('Suppression approvisionnement:', ['id' => $id]);

            // Récupérer l'approvisionnement
            $appro = DB::table('approvisionnements')
                ->where('id', $id)
                ->first();

            if (!$appro) {
                return response()->json(['error' => 'Approvisionnement non trouvé'], 404);
            }

            // Réduire le stock du produit
            DB::table('products')
                ->where('idProduct', $appro->idProduct)
                ->decrement('stock', $appro->qteAppro);

            // Supprimer l'approvisionnement
            DB::table('approvisionnements')
                ->where('id', $id)
                ->delete();

            Log::info('Approvisionnement supprimé avec succès:', ['id' => $id]);

            return response()->json([
                'message' => 'Approvisionnement supprimé avec succès'
            ], 200);

        } catch (\Exception $e) {
            Log::error('Erreur suppression:', ['message' => $e->getMessage()]);
            return response()->json([
                'error' => 'Erreur: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer un approvisionnement spécifique
     */
    public function show($id)
    {
        try {
            $appro = DB::table('approvisionnements as a')
                ->join('products as p', 'a.idProduct', '=', 'p.idProduct')
                ->where('a.id', $id)
                ->select('a.*', 'p.designProduct', 'p.refProduct', 'p.imageProduct')
                ->first();

            if (!$appro) {
                return response()->json(['error' => 'Approvisionnement non trouvé'], 404);
            }

            return response()->json($appro, 200);

        } catch (\Exception $e) {
            Log::error('Erreur récupération:', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'Erreur serveur'], 500);
        }
    }
}