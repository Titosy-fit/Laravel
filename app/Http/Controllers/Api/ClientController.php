<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\Fournisseur;
use App\Models\Code;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\ClientCodeMail;


class ClientController extends Controller
{
    public function register(Request $request)
    {
        // Validation de base
        $validator = Validator::make($request->all(), [
            "nom" => "required|string|max:100",
            "prenom" => "nullable|string|max:100",
            "adresse" => "nullable|string|max:255",
            "email" => "required|email", // supprime unique:clients,email
            "password" => "required|min:6|confirmed",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        // 🔹 Vérification email unique sur toute l'application
        $emailExists = Client::where('email', $request->email)->exists()
            || Fournisseur::where('emailFRN', $request->email)->exists();

        if ($emailExists) {
            return response()->json([
                "status" => "error",
                "errors" => [
                    "email" => ["Cet email est déjà utilisé par un autre utilisateur."]
                ]
            ], 422);
        }

        $verify_email = "non";

        // 1️ Créer le client
        $client = Client::create([
            "nom" => $request->nom,
            "prenom" => $request->prenom,
            "adresse" => $request->adresse,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "verify_email" => $verify_email,
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

        // // 4️ Envoyer le code par email (optionnel)
        
        // Mail::raw("Votre code de confirmation est : $randomCode", function ($message) use ($client) {
        //     $message->to($client->email)
        //             ->subject("Code de confirmation");
        // });
  Mail::to($client->email)->send(new ClientCodeMail($randomCode, $client));
        return response()->json([
            "status" => "success",
            "message" => "Inscription réussie.",
            "client" => $client,
            "code" => $randomCode, // en prod, éviter de renvoyer le code dans la réponse
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

        $client = Client::where("email", $request->email)->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            return response()->json([
                "status" => "error",
                "message" => "Identifiants invalides."
            ], 401);
        }

        $client->tokens()->delete();

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

    // ==================== VERIFICATION EMAIL ====================
    public function verifyEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "email" => "required|email",
            "code" => "required|digits:6",
        ]);

        if ($validator->fails()) {
            return response()->json([
                "status" => "error",
                "errors" => $validator->errors()
            ], 422);
        }

        $client = Client::where("email", $request->email)->first();

        if (!$client) {
            return response()->json([
                "status" => "error",
                "message" => "Client introuvable."
            ], 404);
        }

        $code = Code::where("client_id", $client->id)
            ->where("code", $request->code)
            ->where("typeCode", "validation_email")
            ->latest()
            ->first();

        if (!$code) {
            return response()->json([
                "status" => "error",
                "message" => "Code incorrect ou expiré."
            ], 400);
        }

        $client->verify_email = "oui";
        $client->save();

        $code->delete();

        $token = $client->createToken("clientToken")->plainTextToken;

        return response()->json([
            "status" => "success",
            "message" => "Email vérifié avec succès.",
            "client" => $client,
            "token" => $token,
        ], 200);
    }
}
