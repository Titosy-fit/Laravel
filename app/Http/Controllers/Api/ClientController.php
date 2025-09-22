<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Code;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;

class ClientController extends Controller
{
    public function register(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            "nom" => "required|string|max:100",
            "prenom" => "nullable|string|max:100",
            "adresse" => "nullable|string|max:255",
            "email" => "required|email|unique:clients,email",
            "password" => "required|min:6|confirmed", // password + password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        // 1️ Créer le client
        $client = Client::create([
            "nom" => $request->nom,
            "prenom" => $request->prenom,
            "adresse" => $request->adresse,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);

        // Générer token avec Sanctum
        $token = $client->createToken("clientToken")->plainTextToken;


        // 2️ Générer un code aléatoire à 6 chiffres
        $randomCode = rand(100000, 999999);

        // 3️ Sauvegarder dans la table codes
        $code = Code::create([
            "typeCode" => "validation_email",
            "code" => $randomCode,
            "client_id" => $client->id,
        ]);

        // // 4️ Envoyer le code par email
        // Mail::raw("Votre code de confirmation est : $randomCode", function ($message) use ($client) {
        //     $message->to($client->email)
        //             ->subject("Code de confirmation");
        // });

        return response()->json([
            "status" => "success",
            "message" => "Inscription réussie.",
            "client" => $client,
            "code" => $randomCode, // en prod, évite de renvoyer le code dans la réponse
            "token" => $token,
        ], 201);
    }

    
    // ==================== LOGIN ====================
    public function login(Request $request)
    {
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

        // Vérifier si l'email existe
        $client = Client::where("email", $request->email)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            return response()->json([
                "status" => "error",
                "message" => "Identifiants invalides."
            ], 401);
        }

        // Supprimer les anciens tokens si tu veux forcer 1 seule connexion
        $client->tokens()->delete();

        // Créer un nouveau token
        $token = $client->createToken("clientToken")->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Connexion réussie.",
            "client" => $client,
            "token" => $token,
        ], 200);
    }

    // ==================== LOGOUT ====================
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json([
            "status" => "success",
            "message" => "Déconnexion réussie."
        ]);
    }
}
