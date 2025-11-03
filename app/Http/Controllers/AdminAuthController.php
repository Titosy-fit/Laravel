<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $request->validate([
                'emailAdmin' => 'required|email',
                'passAdmin' => 'required',
            ]);

            $admin = DB::table('admins')->where('emailAdmin', $request->emailAdmin)->first();

            if (!$admin || !Hash::check($request->passAdmin, $admin->passAdmin)) {
                return response()->json(['message' => 'Email ou mot de passe incorrect'], 401);
            }

            return response()->json([
                'message' => 'Connexion réussie',
                'admin' => $admin
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Erreur serveur : ' . $e->getMessage(),
            ], 500);
        }
    }

    public function logout()
    {
        return response()->json(['message' => 'Déconnexion réussie']);
    }



public function getInfo(Request $request)
{
    // Récupère le premier admin pour test
    $admin = DB::table('admins')->first();

    if (!$admin) {
        return response()->json(['error' => 'Admin non trouvé'], 404);
    }

    return response()->json([
        'nomAdmin' => $admin->nomAdmin,
        'emailAdmin' => $admin->emailAdmin
    ]);
}

public function updatePassword(Request $request)
{
    try {
        $admin = DB::table('admins')->first();
        if (!$admin) {
            return response()->json(['error' => 'Admin introuvable'], 401);
        }

        if (!Hash::check($request->ancien, $admin->passAdmin)) {
            return response()->json(['error' => 'Ancien mot de passe incorrect'], 400);
        }

        if ($request->nouveau !== $request->confirmation) {
            return response()->json(['error' => 'Les mots de passe ne correspondent pas'], 400);
        }

        DB::table('admins')->where('idAdmin', $admin->idAdmin)->update([
            'passAdmin' => Hash::make($request->nouveau)
        ]);

        return response()->json(['success' => true]);
    } catch (\Throwable $e) {
        return response()->json(['error' => 'Erreur serveur: '.$e->getMessage()], 500);
    }
}



}