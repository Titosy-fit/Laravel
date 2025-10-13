<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Livreur;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        // === Validation des champs ===
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "password" => "required|min:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        // === Vérifier si c’est un Client ===
        $client = Client::where("email", $request->email)->first();
        if ($client && Hash::check($request->password, $client->password)) {
            $client->tokens()->delete();
            $token = $client->createToken("clientToken")->plainTextToken;

            return response()->json([
                "status" => "success",
                "type"   => "client",
                "user"   => $client,
                "token"  => $token,
                "redirect" => "ClientPage",
            ]);
        }

        // === Vérifier si c’est un Fournisseur ===
        $fournisseur = Fournisseur::where("emailFRN", $request->email)->first();
        if ($fournisseur && Hash::check($request->password, $fournisseur->passwordFRN)) {
            $fournisseur->tokens()->delete();
            $token = $fournisseur->createToken("fournisseurToken")->plainTextToken;

            return response()->json([
                "status" => "success",
                "type"   => "fournisseur",
                "user"   => $fournisseur,
                "token"  => $token,
                "redirect" => "FournisseurPage",
            ]);
        }

        // === Vérifier si c’est un Livreur ===
        $livreur = Livreur::where("emailLivreur", $request->email)->first();
        if ($livreur && Hash::check($request->password, $livreur->passwordLivreur)) {

            $livreur->tokens()->delete();


            $token = $livreur->createToken("livreurToken")->plainTextToken;


            if ($livreur->etatInscription === "en attente") {
                return response()->json([
                    "status" => "error",
                    "message" => "Votre inscription est encore en attente de validation."
                ], 403);
            }


            return response()->json([
                "status" => "success",
                "type"   => "livreur",
                "user"   => $livreur,
                "token"  => $token,
                "redirect" => "LivreurPage",
            ]);
        }

        // === Si aucun utilisateur trouvé ===
        return response()->json([
            "status" => "error",
            "message" => "Identifiants invalides."
        ], 401);
    }



    public function logout(Request $request)
    {
        try {

            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Déconnexion réussie.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la déconnexion : ' . $e->getMessage()
            ], 500);
        }
    }
}
